import { Injectable } from '@angular/core';
import { ApiService } from './api';
import { APPLICANT_TABLE_SCHEMA } from './schemas';

interface Message {
  sender: 'user' | 'ai';
  content: string;
}

@Injectable({
  providedIn: 'root'
})
export class ChatOrchestratorService {
  messages: Message[] = [];
  newMessage: string = '';
  isLoading: boolean = false;
  showThinkingModal: boolean = false;
  currentAiRole: string = 'collaborate';
  execution_context: string[] = [];
  breakdownSteps: string[] = [];
  public thinkingMessage: string = 'Thinking...';
  public breakdownRetryCount: number = 0;
  private readonly MAX_BREAKDOWN_RETRIES: number = 2;
  private currentStepIndex: number = 0;
  private queryRetryCount: number = 0;
  private readonly MAX_QUERY_RETRIES: number = 5;
  private currentUserMessageForHistory: string = '';

  constructor(private apiService: ApiService) { }

  private cleanAiContent(content: string): string {
    return content.replace(/\s*[[.*?]\s*/g, ' ').trim();
  }

  private getAiRolePrompt(): string {
    const dbSchema = APPLICANT_TABLE_SCHEMA;
    switch (this.currentAiRole) {
      case 'collaborate':
        return `You are a Collaboration AI for a deployment agency system. Your purpose is to act as a helpful assistant, clarifying the user's needs to generate a precise context for subsequent AI agents. Reply in short, easy-to-understand messages and avoid technical terms. Your goal is to fully understand what the user wants to achieve. Crucially, you must not ask about or discuss the final output format (e.g., CSV, JSON, HTML). The system handles all formatting automatically. Your sole focus is to understand the user's goal. When you have a clear understanding and a detailed context, output the trigger [[COLLAB_DONE]].\n\nAvailable Database Schema for your reference:\n${dbSchema}`;
      case 'analyze':
        return `You are an Analysis AI. Your task is to summarize the user's intent and the clarified context from the Collaboration AI into a concise brief for the Breakdown AI. You will receive a structured context object.\n\n        Your output MUST be a detailed description of what the user needs.\n\n        Available Database Schema:\n        ${dbSchema}`;
      case 'breakdown':
        return `You are an expert AI assistant whose sole purpose is to break down a given task into a series of discrete, ordered, and actionable steps. The output must be a JSON array of strings, where each string is a single step. Do not include any other text or conversational filler. The database schema is provided below for context when formulating steps that involve data retrieval or manipulation:\n\n${APPLICANT_TABLE_SCHEMA}\n\n"Retrieve enterprise users inactive for >30 days", your output should be: ["Find the 'enterprise' plan ID.", "Query the 'applicant' table for users matching the plan ID and last_login > 30 days ago.", "Format the final user list for display."]`;
      case 'execution':
        return `You are an Execution AI. Your task is to process a single step from a breakdown plan. Determine if the step requires a database query. If it does, output [[QUERY_REQUIRED]] followed by a natural language description of the query needed. If it does not, output [[STEP_COMPLETE]] followed by a confirmation or the result of the internal action. The database schema is provided for context:\n\n${APPLICANT_TABLE_SCHEMA}`;
      case 'query_generation':
        return `You are a Query Generation AI. Your task is to convert a natural language query description into a valid SQL query. Use the provided database schema for reference. Output ONLY the SQL query string. Do not include any other text or conversational filler. If you cannot generate a valid SQL query, output an empty string or an error message. The database schema is provided for context:\n\n${APPLICANT_TABLE_SCHEMA}`;
      case 'safety_check':
        return `You are a Safety AI. Your task is to analyze a given SQL query for potential risks, destructive commands, or any unsafe operations. Respond with [[SAFE_TO_RUN]] if the query is safe for execution, or [[UNSAFE]] if it poses a risk. Do not include any other text or conversational filler.`;
      case 'finalization':
        return `You are a Finalization AI. Your task is to aggregate all results and produce a final, user-facing summary or answer. Synthesize the information into a coherent and human-readable response.`;
      case 'html_conversion':
        return `You are an HTML Conversion AI. Your task is to convert the final AI response to HTML using Tailwind CSS for styling (which is already installed). Make sure to use a generous amount of Font Awesome icons to enhance the visual presentation. Do not include any other text or conversational filler.`;
      default:
        return `You are a helpful AI assistant.`;
    }
  }

  sendMessage(): void {
    if (this.newMessage.trim() === '') {
      return;
    }

    this.messages.push({
      sender: 'user',
      content: this.newMessage
    });

    const userMessage = this.newMessage;
    this.currentUserMessageForHistory = userMessage;
    this.newMessage = '';

    this.isLoading = true;
    this.thinkingMessage = 'Thinking...';

    const contextMessages = this.messages.slice(-10).map(msg => ({
      role: msg.sender === 'user' ? 'user' : 'assistant',
      content: msg.content
    }));

    contextMessages.unshift({ role: 'system', content: this.getAiRolePrompt() });

    this.apiService.getAiResponse(contextMessages, userMessage, this.currentAiRole).subscribe({
      next: (response: any) => {
        let rawAiContent = response.choices?.[0]?.message?.content;
        let displayContent = rawAiContent || 'No response from AI.';

        if (rawAiContent && rawAiContent.includes('[[COLLAB_DONE]]')) {
          this.thinkingMessage = 'Analyzing request...';
          this.currentAiRole = 'analyze';

          const analysisPrompt = this.getAiRolePrompt();
          const analysisContextMessages = [...contextMessages];
          analysisContextMessages.unshift({ role: 'system', content: analysisPrompt });

          this.apiService.getAiResponse(analysisContextMessages, '', this.currentAiRole).subscribe({
            next: (analysisResponse: any) => {
              let rawAnalysisContent = analysisResponse.choices?.[0]?.message?.content;
              let displayAnalysisContent = rawAnalysisContent || 'No response from Analysis AI.';

              this.execution_context.push(displayAnalysisContent);
              console.log('Analysis AI output stored in execution_context:', this.execution_context);

              this.thinkingMessage = 'Breaking down the task into steps...';
              this.currentAiRole = 'breakdown';
              const breakdownPrompt = this.getAiRolePrompt();
              const analysisOutputForBreakdown = this.execution_context[this.execution_context.length - 1];

              const breakdownContextMessages = [
                { role: 'system', content: breakdownPrompt },
                { role: 'user', content: analysisOutputForBreakdown }
              ];

              this.apiService.getAiResponse(breakdownContextMessages, '', this.currentAiRole).subscribe({
                next: (res) => this.handleBreakdownResponse(res),
                error: (err) => this.handleBreakdownError(err)
              });

            },
            error: (analysisError: any) => {
              console.error('Error fetching Analysis AI response:', analysisError);
              this.messages.push({
                sender: 'ai',
                content: 'Error: Could not get a response from the Analysis AI.'
              });
              this.isLoading = false;
              this.showThinkingModal = false;
            }
          });
        } else {
          displayContent = this.cleanAiContent(displayContent);
          this.messages.push({
            sender: 'ai',
            content: displayContent
          });
          this.isLoading = false;
          this.showThinkingModal = false;
          this.apiService.saveChatHistory(userMessage, displayContent).subscribe({
            next: (saveResponse) => console.log('Chat history saved:', saveResponse),
            error: (saveError) => console.error('Error saving chat history:', saveError)
          });
        }
      },
      error: (error: any) => {
        console.error('Error fetching AI response:', error);
        this.messages.push({
          sender: 'ai',
          content: 'Error: Could not get a response from the AI.'
        });
        this.isLoading = false;
        this.showThinkingModal = false;
      }
    });
  }

  private handleBreakdownResponse(breakdownResponse: any): void {
    let rawBreakdownContent = breakdownResponse.choices?.[0]?.message?.content;
    if (rawBreakdownContent) {
      try {
        const parsedSteps = JSON.parse(rawBreakdownContent);
        if (Array.isArray(parsedSteps) && parsedSteps.every(step => typeof step === 'string')) {
          this.breakdownSteps = parsedSteps;
          console.log('Breakdown AI steps:', this.breakdownSteps);
          this.breakdownRetryCount = 0;
          this.thinkingMessage = 'Preparing for execution...';
          this.executeNextStep(0);
        } else {
          throw new Error('Parsed content is not a JSON array of strings.');
        }
      } catch (e) {
        console.error('Error parsing Breakdown AI response or invalid format:', e);
        if (this.breakdownRetryCount < this.MAX_BREAKDOWN_RETRIES) {
          this.breakdownRetryCount++;
          console.warn(`Breakdown AI retry attempt ${this.breakdownRetryCount}/${this.MAX_BREAKDOWN_RETRIES}`);

          const retryPrompt = this.getAiRolePrompt();
          const feedbackMessage = `Previous response was not a valid JSON array of strings. Please provide the breakdown again in the correct format: ["step 1", "step 2"].`;
          const analysisOutputForBreakdown = this.execution_context[this.execution_context.length - 1];

          const breakdownContextMessages = [
            { role: 'system', content: retryPrompt },
            { role: 'user', content: analysisOutputForBreakdown },
            { role: 'assistant', content: rawBreakdownContent },
            { role: 'user', content: feedbackMessage }
          ];

          this.apiService.getAiResponse(breakdownContextMessages, '', this.currentAiRole).subscribe({
            next: (res) => this.handleBreakdownResponse(res),
            error: (err) => this.handleBreakdownError(err)
          });
        } else {
          this.messages.push({
            sender: 'ai',
            content: 'Error: Breakdown AI failed to produce valid steps after multiple attempts.'
          });
          this.isLoading = false;
          this.showThinkingModal = false;
          this.breakdownRetryCount = 0;
        }
      }
    } else {
      this.messages.push({
        sender: 'ai',
        content: 'Error: No response from Breakdown AI.'
      });
      this.isLoading = false;
      this.showThinkingModal = false;
      this.breakdownRetryCount = 0;
    }
  }

  private handleBreakdownError(breakdownError: any): void {
    console.error('Error fetching Breakdown AI response:', breakdownError);
    this.messages.push({
      sender: 'ai',
      content: 'Error: Could not get a response from the Breakdown AI.'
    });
    this.isLoading = false;
    this.showThinkingModal = false;
    this.breakdownRetryCount = 0;
  }

  private executeNextStep(stepIndex: number): void {
    if (stepIndex >= this.breakdownSteps.length) {
      console.log('All breakdown steps executed. Triggering Finalization AI.');
      this.thinkingMessage = 'Finalizing the response...';
      this.currentAiRole = 'finalization';
      const finalizationPrompt = this.getAiRolePrompt();
      const finalizationContextMessages = [
        { role: 'system', content: finalizationPrompt },
        ...this.execution_context.map(content => ({ role: 'assistant', content: content }))
      ];

      this.apiService.getAiResponse(finalizationContextMessages, '', this.currentAiRole).subscribe({
        next: (finalizationResponse: any) => {
          let rawFinalizationContent = finalizationResponse.choices?.[0]?.message?.content;
          let displayFinalizationContent = rawFinalizationContent || 'No finalization message from AI.';

          displayFinalizationContent = this.cleanAiContent(displayFinalizationContent);

          this.thinkingMessage = 'Converting to HTML...';
          this.currentAiRole = 'html_conversion';
          const conversionPrompt = this.getAiRolePrompt();
          const contentToConvert = displayFinalizationContent;

          const conversionContextMessages = [
            { role: 'system', content: conversionPrompt },
            { role: 'user', content: contentToConvert }
          ];

          this.apiService.getAiResponse(conversionContextMessages, '', this.currentAiRole).subscribe({
            next: (conversionResponse: any) => {
              let rawHtmlContent = conversionResponse.choices?.[0]?.message?.content;
              let displayHtmlContent = rawHtmlContent || 'No HTML response from AI.';

              this.messages.push({
                sender: 'ai',
                content: displayHtmlContent
              });
              this.isLoading = false;
              this.showThinkingModal = false;

              this.apiService.saveChatHistory(this.currentUserMessageForHistory, displayHtmlContent).subscribe({
                next: (saveResponse) => console.log('HTML content saved:', saveResponse),
                error: (saveError) => console.error('Error saving HTML content:', saveError)
              });
            },
            error: (conversionError: any) => {
              console.error('Error fetching HTML Conversion AI response:', conversionError);
              this.messages.push({
                sender: 'ai',
                content: 'Error: Could not get a response from the HTML Conversion AI.'
              });
              this.isLoading = false;
              this.showThinkingModal = false;
            }
          });
        },
        error: (finalizationError: any) => {
          console.error('Error fetching Finalization AI response:', finalizationError);
          this.messages.push({
            sender: 'ai',
            content: 'Error: Could not get a finalization message from the AI.'
          });
          this.isLoading = false;
          this.showThinkingModal = false;
        }
      });
      return;
    }

    this.currentStepIndex = stepIndex;
    this.currentAiRole = 'execution';
    const currentStep = this.breakdownSteps[this.currentStepIndex];
    this.thinkingMessage = `Executing step ${this.currentStepIndex + 1}/${this.breakdownSteps.length}: ${currentStep}`;
    console.log(`Executing step ${this.currentStepIndex + 1}/${this.breakdownSteps.length}: ${currentStep}`);

    const executionPrompt = this.getAiRolePrompt();
    const executionContextMessages = [
      { role: 'system', content: executionPrompt },
      ...this.execution_context.map(content => ({ role: 'assistant', content: content })),
      { role: 'user', content: currentStep }
    ];

    this.apiService.getAiResponse(executionContextMessages, '', this.currentAiRole).subscribe({
      next: (executionResponse: any) => {
        let rawExecutionContent = executionResponse.choices?.[0]?.message?.content;
        if (rawExecutionContent) {
          this.execution_context.push(rawExecutionContent);
          console.log('Execution AI output:', rawExecutionContent);

          if (rawExecutionContent.includes('[[QUERY_REQUIRED]]')) {
            const naturalLanguageQuery = rawExecutionContent.replace('[[QUERY_REQUIRED]]', '').trim();
            console.log('Query required:', naturalLanguageQuery);
            this.processQueryStep(naturalLanguageQuery);
          } else if (rawExecutionContent.includes('[[STEP_COMPLETE]]')) {
            console.log('Step complete:', rawExecutionContent.replace('[[STEP_COMPLETE]]', '').trim());
            this.executeNextStep(this.currentStepIndex + 1);
          } else {
            console.log('Execution AI performed internal action or provided direct output:', rawExecutionContent);
            this.executeNextStep(this.currentStepIndex + 1);
          }
        } else {
          console.error('Error: No response from Execution AI for step:', currentStep);
          this.messages.push({
            sender: 'ai',
            content: `Error: No response from Execution AI for step "${currentStep}".`
          });
          this.isLoading = false;
          this.showThinkingModal = false;
        }
      },
      error: (executionError: any) => {
        console.error('Error fetching Execution AI response:', executionError);
        this.messages.push({
          sender: 'ai',
          content: `Error: Could not get a response from the Execution AI for step "${currentStep}".`
        });
        this.isLoading = false;
        this.showThinkingModal = false;
      }
    });
  }

  private processQueryStep(naturalLanguageQuery: string): void {
    this.currentAiRole = 'query_generation';
    this.thinkingMessage = 'Generating SQL query...';
    console.log('Generating SQL query for:', naturalLanguageQuery);

    const queryGenerationPrompt = this.getAiRolePrompt();
    const queryGenerationContextMessages = [
      { role: 'system', content: queryGenerationPrompt },
      ...this.execution_context.map(content => ({ role: 'assistant', content: content })),
      { role: 'user', content: naturalLanguageQuery }
    ];

    if (this.queryRetryCount > 0) {
      queryGenerationContextMessages.push({
        role: 'user',
        content: `Previous attempt failed. Please ensure the SQL query is valid and directly executable. Avoid any conversational text.`
      });
    }

    this.apiService.getAiResponse(queryGenerationContextMessages, '', this.currentAiRole).subscribe({
      next: (queryResponse: any) => {
        let rawSqlQuery = queryResponse.choices?.[0]?.message?.content;
        if (rawSqlQuery) {
          this.execution_context.push(`Generated SQL: ${rawSqlQuery}`);
          console.log('Generated SQL:', rawSqlQuery);

          this.thinkingMessage = 'Performing AI safety check on query...';
          this.currentAiRole = 'safety_check';
          const safetyCheckPrompt = this.getAiRolePrompt();
          const safetyCheckContextMessages = [
            { role: 'system', content: safetyCheckPrompt },
            { role: 'user', content: rawSqlQuery }
          ];

          this.apiService.getAiResponse(safetyCheckContextMessages, '', this.currentAiRole).subscribe({
            next: (safetyResponse: any) => {
              let rawSafetyContent = safetyResponse.choices?.[0]?.message?.content;
              if (rawSafetyContent && rawSafetyContent.includes('[[SAFE_TO_RUN]]')) {
                console.log('SQL query passed AI safety check.');
                this.execution_context.push(`AI Safety Check: [[SAFE_TO_RUN]]`);
                this.thinkingMessage = 'Executing SQL query...';
                this.apiService.executeQuery(rawSqlQuery, []).subscribe({
                  next: (queryResult: any) => {
                    this.execution_context.push(`Query Result: ${JSON.stringify(queryResult)}`);
                    console.log('Query Result:', queryResult);
                    this.queryRetryCount = 0;
                    this.executeNextStep(this.currentStepIndex + 1);
                  },
                  error: (queryError: any) => {
                    console.error('Error executing SQL query:', queryError);
                    if (this.queryRetryCount < this.MAX_QUERY_RETRIES) {
                      this.queryRetryCount++;
                      console.warn(`Query execution retry attempt ${this.queryRetryCount}/${this.MAX_QUERY_RETRIES}`);
                      this.execution_context.push(`Query execution failed: ${queryError.message}. Please correct the SQL query.`);
                      this.processQueryStep(naturalLanguageQuery);
                    } else {
                      this.messages.push({
                        sender: 'ai',
                        content: `Error: Failed to execute query after ${this.MAX_QUERY_RETRIES} attempts. Please refine your request.`
                      });
                      this.isLoading = false;
                      this.showThinkingModal = false;
                      this.queryRetryCount = 0;
                    }
                  }
                });
              } else {
                console.warn('SQL query failed AI safety check:', rawSqlQuery);
                this.execution_context.push(`AI Safety Check: [[UNSAFE]]`);
                if (this.queryRetryCount < this.MAX_QUERY_RETRIES) {
                  this.queryRetryCount++;
                  console.warn(`Query safety retry attempt ${this.queryRetryCount}/${this.MAX_QUERY_RETRIES}`);
                  this.execution_context.push(`SQL query failed AI safety check. Please generate a safe query.`);
                  this.processQueryStep(naturalLanguageQuery);
                } else {
                  this.messages.push({
                    sender: 'ai',
                    content: `Error: Failed to generate a safe query after ${this.MAX_QUERY_RETRIES} attempts. Please refine your request.`
                  });
                  this.isLoading = false;
                  this.showThinkingModal = false;
                  this.queryRetryCount = 0;
                }
              }
            },
            error: (safetyError: any) => {
              console.error('Error fetching Safety AI response:', safetyError);
              this.messages.push({
                sender: 'ai',
                content: `Error: Could not get a response from the Safety AI for query "${rawSqlQuery}".`
              });
              this.isLoading = false;
              this.showThinkingModal = false;
              this.queryRetryCount = 0;
            }
          });
        } else {
          console.error('Error: No SQL query generated for:', naturalLanguageQuery);
          if (this.queryRetryCount < this.MAX_QUERY_RETRIES) {
            this.queryRetryCount++;
            console.warn(`Query generation retry attempt ${this.queryRetryCount}/${this.MAX_QUERY_RETRIES}`);
            this.execution_context.push(`No SQL query was generated. Please generate a valid SQL query.`);
            this.processQueryStep(naturalLanguageQuery);
          } else {
            this.messages.push({
              sender: 'ai',
              content: `Error: Failed to generate a SQL query after ${this.MAX_QUERY_RETRIES} attempts. Please refine your request.`
            });
            this.isLoading = false;
            this.showThinkingModal = false;
            this.queryRetryCount = 0;
          }
        }
      },
      error: (queryGenerationError: any) => {
        console.error('Error fetching Query Generation AI response:', queryGenerationError);
        this.messages.push({
          sender: 'ai',
          content: `Error: Could not get a response from the Query Generation AI for "${naturalLanguageQuery}".`
        });
        this.isLoading = false;
        this.showThinkingModal = false;
        this.queryRetryCount = 0;
      }
    });
  }
}
