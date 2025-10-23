import { Component, ViewChild, ElementRef, AfterViewChecked, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms'; // Import FormsModule
import { ApiService } from './api'; // Corrected import path for ApiService
import { APPLICANT_TABLE_SCHEMA } from './schemas'; // Import the schema

interface Message {
  sender: 'user' | 'ai';
  content: string;
}

const MAX_TEXTAREA_HEIGHT = 150; // Maximum height for the textarea in pixels

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, FormsModule], // Add FormsModule here
  templateUrl: './app.component.html',
  styleUrl: './app.css'
})
export class AppComponent implements AfterViewChecked, OnInit {
  title = 'analytics-agent';
  messages: Message[] = [];
  newMessage: string = '';
  isLoading: boolean = false; // Add isLoading property
  showThinkingModal: boolean = false; // Add showThinkingModal property
  currentAiRole: string = 'collaborate'; // Initialize AI role
  execution_context: string[] = []; // New property to store execution context
  breakdownSteps: string[] = []; // New property to store breakdown steps
  public thinkingMessage: string = 'Thinking...'; // New property for dynamic status
  private breakdownRetryCount: number = 0; // Counter for Breakdown AI retries
  private readonly MAX_BREAKDOWN_RETRIES: number = 2; // Max retries for Breakdown AI
  private currentStepIndex: number = 0; // To track the current step being executed
  private queryRetryCount: number = 0; // Counter for Query AI retries
  private readonly MAX_QUERY_RETRIES: number = 5; // Max retries for Query AI
  private currentUserMessageForHistory: string = ''; // New property to store the original user message for history saving

  @ViewChild('chatContainer') private chatContainer!: ElementRef;
  @ViewChild('messageInput') private messageInput!: ElementRef;

  constructor(private apiService: ApiService) { }

  ngOnInit(): void {
    // Load chat history on component initialization
    this.apiService.getChatHistory().subscribe({
      next: (history: any) => {
        if (history && history.data && history.data.length > 0) {
          // Clear initial AI message if history is loaded
          this.messages = [];
          // Reverse the order to display chronologically
          const sortedHistory = history.data.reverse();
          sortedHistory.forEach((item: any) => {
            this.messages.push({ sender: 'user', content: item.message });
            this.messages.push({ sender: 'ai', content: this.cleanAiContent(item.reply) }); // Clean old replies
          });
        } else {
          // If no history, add initial AI message
          this.messages.push({
            sender: 'ai',
            content: 'Hello! How can I help you today?'
          });
        }
      },
      error: (error) => {
        console.error('Error loading chat history:', error);
        // If error loading history, add initial AI message
        this.messages.push({
          sender: 'ai',
          content: 'Hello! How can I help you today?'
        });
      }
    });
  }

  ngAfterViewChecked(): void {
    this.scrollToBottom();
  }

  public adjustTextareaHeight(): void {
    if (this.messageInput && this.messageInput.nativeElement) {
      const element = this.messageInput.nativeElement;
      element.style.height = 'auto';
      element.style.height = Math.min(element.scrollHeight, MAX_TEXTAREA_HEIGHT) + 'px';
      element.style.overflowY = element.scrollHeight > MAX_TEXTAREA_HEIGHT ? 'auto' : 'hidden';
    }
  }

  private cleanAiContent(content: string): string {
    return content.replace(/(\[\[.*?\]\])/g, '').trim();
  }

  private getAiRolePrompt(): string {
    const dbSchema = APPLICANT_TABLE_SCHEMA;
    switch (this.currentAiRole) {
      case 'collaborate':
        return `You are a Collaboration AI for a deployment agency system. Besides General assistance, Your goal is to clarify the user\'s needs and generate a precise initial context for subsequent AI agents. Your primary function is to help manage the application by getting or manipulating data through queries. reply in short messages. Avoid mentioning technical terms. ALWAYS use easy-to-understand language. Make sure we fully understand what the user needs. When you have a detailed context object, output it followed by the trigger [[COLLAB_DONE]].\n\nAvailable Database Schema:\n${dbSchema}`;
      case 'analyze':
        return `You are an Analysis AI. Your task is to summarize the user's intent and the clarified context from the Collaboration AI into a concise brief for the Breakdown AI. You will receive a structured context object.

        Your output MUST be a detailed description of what the user needs.

        Available Database Schema:
        ${dbSchema}`;
      // Add other roles as needed
      case 'breakdown':
        return `You are an expert AI assistant whose sole purpose is to break down a given task into a series of discrete, ordered, and actionable steps. The output must be a JSON array of strings, where each string is a single step. Do not include any other text or conversational filler. The database schema is provided below for context when formulating steps that involve data retrieval or manipulation:\n\n${APPLICANT_TABLE_SCHEMA}\n\nFor example, if the task is "Retrieve enterprise users inactive for >30 days", your output should be: ["Find the 'enterprise' plan ID.", "Query the 'applicant' table for users matching the plan ID and last_login > 30 days ago.", "Format the final user list for display."].`;
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

    // Add user message
    this.messages.push({
      sender: 'user',
      content: this.newMessage
    });

    const userMessage = this.newMessage;
    this.currentUserMessageForHistory = userMessage; // Store the original user message
    this.newMessage = ''; // Clear input immediately
    this.adjustTextareaHeight(); // Call the correct adjust method

    this.isLoading = true; // Set loading to true before API call
    this.thinkingMessage = 'Thinking...'; // Set initial thinking message

    // Prepare context for AI (last 10 messages, 5 user and 5 AI)
    const contextMessages = this.messages.slice(-10).map(msg => ({
      role: msg.sender === 'user' ? 'user' : 'assistant',
      content: msg.content
    }));

    // Add the AI role prompt to the beginning of the context messages
    contextMessages.unshift({ role: 'system', content: this.getAiRolePrompt() });

    // Call AI service with the current AI role
    this.apiService.getAiResponse(contextMessages, userMessage, this.currentAiRole).subscribe({
      next: (response: any) => { // Type as any for now, or define a more specific interface if needed
        let rawAiContent = response.choices?.[0]?.message?.content;
        let displayContent = rawAiContent || 'No response from AI.'; // Initialize with raw or default

        // Check for COLLAB_DONE trigger BEFORE cleaning for display
        if (rawAiContent && rawAiContent.includes('[[COLLAB_DONE]]')) {
          this.thinkingMessage = 'Analyzing request...'; // Update for analysis phase
          this.currentAiRole = 'analyze';

          // The Analysis AI will analyze the entire conversation history
          const analysisPrompt = this.getAiRolePrompt(); // Get the prompt for Analysis AI
          // Pass the entire contextMessages (which includes the system prompt and recent history)
          // to the Analysis AI for it to analyze the conversation. The last message in contextMessages will be the Collaboration AI's response with [[COLLAB_DONE]].
          const analysisContextMessages = [...contextMessages]; // Create a copy
          analysisContextMessages.unshift({ role: 'system', content: analysisPrompt }); // Add Analysis AI's system prompt

          // Make a new API call for Analysis AI
          // The 'message' parameter here is the *user's last message* for the AI to respond to,
          // but for an internal AI transition, we can pass an empty string or a specific internal trigger.
          // The actual context for analysis is in analysisContextMessages.
          this.apiService.getAiResponse(analysisContextMessages, '', this.currentAiRole).subscribe({
            next: (analysisResponse: any) => {
              let rawAnalysisContent = analysisResponse.choices?.[0]?.message?.content;
              let displayAnalysisContent = rawAnalysisContent || 'No response from Analysis AI.';

              displayAnalysisContent = this.cleanAiContent(displayAnalysisContent);

              this.messages.push({
                sender: 'ai',
                content: displayAnalysisContent
              });
              this.isLoading = false;
              this.showThinkingModal = false;

              // Store the Analysis AI's response in the execution_context array for later use
              this.execution_context.push(displayAnalysisContent);
              console.log('Analysis AI output stored in execution_context:', this.execution_context);

              // --- Trigger Breakdown AI ---
              this.thinkingMessage = 'Breaking down the task into steps...'; // Update for breakdown phase
              this.currentAiRole = 'breakdown';
              const breakdownPrompt = this.getAiRolePrompt();
              const analysisOutputForBreakdown = this.execution_context[this.execution_context.length - 1]; // Get the latest analysis output

              const breakdownContextMessages = [
                { role: 'system', content: breakdownPrompt },
                { role: 'user', content: analysisOutputForBreakdown } // Pass analysis output as user message to breakdown AI
              ];

              this.apiService.getAiResponse(breakdownContextMessages, '', this.currentAiRole).subscribe({
                next: (res) => this.handleBreakdownResponse(res),
                error: (err) => this.handleBreakdownError(err)
              });
              // --- End Trigger Breakdown AI ---

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
          // If no trigger, just display and save the original AI message
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

  toggleThinkingModal(): void {
    this.showThinkingModal = !this.showThinkingModal;
  }

  private scrollToBottom(): void {
    try {
      this.chatContainer.nativeElement.scrollTop = this.chatContainer.nativeElement.scrollHeight;
    } catch (err) { /* Error handling for when element is not yet available */ }
  }

  private handleBreakdownResponse(breakdownResponse: any): void {
    let rawBreakdownContent = breakdownResponse.choices?.[0]?.message?.content;
    if (rawBreakdownContent) {
      try {
        const parsedSteps = JSON.parse(rawBreakdownContent);
        if (Array.isArray(parsedSteps) && parsedSteps.every(step => typeof step === 'string')) {
          this.breakdownSteps = parsedSteps;
          console.log('Breakdown AI steps:', this.breakdownSteps);
          this.breakdownRetryCount = 0; // Reset retry count on success
          this.isLoading = false;
          this.showThinkingModal = false;
          this.thinkingMessage = 'Preparing for execution...'; // Update for execution phase
          this.executeNextStep(0); // Start executing the breakdown steps
        } else {
          throw new Error('Parsed content is not a JSON array of strings.');
        }
      } catch (e) {
        console.error('Error parsing Breakdown AI response or invalid format:', e);
        if (this.breakdownRetryCount < this.MAX_BREAKDOWN_RETRIES) {
          this.breakdownRetryCount++;
          console.warn(`Breakdown AI retry attempt ${this.breakdownRetryCount}/${this.MAX_BREAKDOWN_RETRIES}`);

          // Construct a new prompt for retry
          const retryPrompt = this.getAiRolePrompt();
          const feedbackMessage = `Previous response was not a valid JSON array of strings. Please provide the breakdown again in the correct format: ["step 1", "step 2"].`;
          const analysisOutputForBreakdown = this.execution_context[this.execution_context.length - 1];

          const breakdownContextMessages = [
            { role: 'system', content: retryPrompt },
            { role: 'user', content: analysisOutputForBreakdown }, // Original input
            { role: 'assistant', content: rawBreakdownContent }, // Previous incorrect response
            { role: 'user', content: feedbackMessage } // Feedback
          ];

          // Re-call the Breakdown AI
          this.apiService.getAiResponse(breakdownContextMessages, '', this.currentAiRole).subscribe({
            next: (res) => this.handleBreakdownResponse(res), // Recursive call to handle response
            error: (err) => this.handleBreakdownError(err)
          });
        } else {
          this.messages.push({
            sender: 'ai',
            content: 'Error: Breakdown AI failed to produce valid steps after multiple attempts.'
          });
          this.isLoading = false;
          this.showThinkingModal = false;
          this.breakdownRetryCount = 0; // Reset for future interactions
        }
      }
    } else {
      this.messages.push({
        sender: 'ai',
        content: 'Error: No response from Breakdown AI.'
      });
      this.isLoading = false;
      this.showThinkingModal = false;
      this.breakdownRetryCount = 0; // Reset for future interactions
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
    this.breakdownRetryCount = 0; // Reset for future interactions
  }

  private executeNextStep(stepIndex: number): void {
    if (stepIndex >= this.breakdownSteps.length) {
      console.log('All breakdown steps executed. Triggering Finalization AI.');
      this.thinkingMessage = 'Finalizing the response...'; // Update for finalization phase
      this.currentAiRole = 'finalization';
      const finalizationPrompt = this.getAiRolePrompt();
      const finalizationContextMessages = [
        { role: 'system', content: finalizationPrompt },
        ...this.execution_context.map(content => ({ role: 'assistant', content: content })) // Include all previous AI outputs
      ];

      this.apiService.getAiResponse(finalizationContextMessages, '', this.currentAiRole).subscribe({
        next: (finalizationResponse: any) => {
          let rawFinalizationContent = finalizationResponse.choices?.[0]?.message?.content;
          let displayFinalizationContent = rawFinalizationContent || 'No finalization message from AI.';

          displayFinalizationContent = this.cleanAiContent(displayFinalizationContent);

          // --- Trigger HTML Conversion ---
          this.thinkingMessage = 'Converting to HTML...'; // Update for HTML conversion phase
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

              // Save the HTML content to chat history
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
          // --- End Trigger HTML Conversion ---
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

    // Prepare context for Execution AI
    const executionPrompt = this.getAiRolePrompt();
    const executionContextMessages = [
      { role: 'system', content: executionPrompt },
      ...this.execution_context.map(content => ({ role: 'assistant', content: content })),
      { role: 'user', content: currentStep } // The current step as the user message for the Execution AI
    ];

    this.apiService.getAiResponse(executionContextMessages, '', this.currentAiRole).subscribe({
      next: (executionResponse: any) => {
        let rawExecutionContent = executionResponse.choices?.[0]?.message?.content;
        if (rawExecutionContent) {
          this.execution_context.push(rawExecutionContent); // Store Execution AI's output
          console.log('Execution AI output:', rawExecutionContent);

          if (rawExecutionContent.includes('[[QUERY_REQUIRED]]')) {
            const naturalLanguageQuery = rawExecutionContent.replace('[[QUERY_REQUIRED]]', '').trim();
            console.log('Query required:', naturalLanguageQuery);
            this.processQueryStep(naturalLanguageQuery); // Trigger the Query Loop
          } else if (rawExecutionContent.includes('[[STEP_COMPLETE]]')) {
            console.log('Step complete:', rawExecutionContent.replace('[[STEP_COMPLETE]]', '').trim());
            this.executeNextStep(this.currentStepIndex + 1); // Move to the next step
          } else {
            // Handle other types of execution AI output (e.g., direct answers, internal actions)
            console.log('Execution AI performed internal action or provided direct output:', rawExecutionContent);
            this.executeNextStep(this.currentStepIndex + 1); // Move to the next step
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
    this.thinkingMessage = 'Generating SQL query...'; // Update for query generation phase
    console.log('Generating SQL query for:', naturalLanguageQuery);

    // Prepare context for Query Generation AI
    const queryGenerationPrompt = this.getAiRolePrompt();
    const queryGenerationContextMessages = [
      { role: 'system', content: queryGenerationPrompt },
      ...this.execution_context.map(content => ({ role: 'assistant', content: content })),
      { role: 'user', content: naturalLanguageQuery } // The natural language query as the user message
    ];

    // If retrying, add feedback to the prompt
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
          this.execution_context.push(`Generated SQL: ${rawSqlQuery}`); // Store generated SQL
          console.log('Generated SQL:', rawSqlQuery);

          // --- AI-Powered Safety Check ---
          this.thinkingMessage = 'Performing AI safety check on query...'; // Update for safety check phase
          this.currentAiRole = 'safety_check';
          const safetyCheckPrompt = this.getAiRolePrompt();
          const safetyCheckContextMessages = [
            { role: 'system', content: safetyCheckPrompt },
            { role: 'user', content: rawSqlQuery } // Pass the generated SQL query to the Safety AI
          ];

          this.apiService.getAiResponse(safetyCheckContextMessages, '', this.currentAiRole).subscribe({
            next: (safetyResponse: any) => {
              let rawSafetyContent = safetyResponse.choices?.[0]?.message?.content;
              if (rawSafetyContent && rawSafetyContent.includes('[[SAFE_TO_RUN]]')) {
                console.log('SQL query passed AI safety check.');
                this.execution_context.push(`AI Safety Check: [[SAFE_TO_RUN]]`); // Store safety check result
                // Execute the query
                this.thinkingMessage = 'Executing SQL query...'; // Update for query execution phase
                this.apiService.executeQuery(rawSqlQuery, []).subscribe({
                  next: (queryResult: any) => {
                    this.execution_context.push(`Query Result: ${JSON.stringify(queryResult)}`); // Store query result
                    console.log('Query Result:', queryResult);
                    this.queryRetryCount = 0; // Reset retry count on success
                    this.executeNextStep(this.currentStepIndex + 1); // Move to the next breakdown step
                  },
                  error: (queryError: any) => {
                    console.error('Error executing SQL query:', queryError);
                    if (this.queryRetryCount < this.MAX_QUERY_RETRIES) {
                      this.queryRetryCount++;
                      console.warn(`Query execution retry attempt ${this.queryRetryCount}/${this.MAX_QUERY_RETRIES}`);
                      // Retry query generation with error feedback
                      this.execution_context.push(`Query execution failed: ${queryError.message}. Please correct the SQL query.`);
                      this.processQueryStep(naturalLanguageQuery); // Re-trigger query generation
                    } else {
                      this.messages.push({
                        sender: 'ai',
                        content: `Error: Failed to execute query after ${this.MAX_QUERY_RETRIES} attempts. Please refine your request.`
                      });
                      this.isLoading = false;
                      this.showThinkingModal = false;
                      this.queryRetryCount = 0; // Reset for future interactions
                    }
                  }
                });
              } else {
                console.warn('SQL query failed AI safety check:', rawSqlQuery);
                this.execution_context.push(`AI Safety Check: [[UNSAFE]]`); // Store safety check result
                if (this.queryRetryCount < this.MAX_QUERY_RETRIES) {
                  this.queryRetryCount++;
                  console.warn(`Query safety retry attempt ${this.queryRetryCount}/${this.MAX_QUERY_RETRIES}`);
                  // Retry query generation with safety feedback
                  this.execution_context.push(`SQL query failed AI safety check. Please generate a safe query.`);
                  this.processQueryStep(naturalLanguageQuery); // Re-trigger query generation
                } else {
                  this.messages.push({
                    sender: 'ai',
                    content: `Error: Failed to generate a safe query after ${this.MAX_QUERY_RETRIES} attempts. Please refine your request.`
                  });
                  this.isLoading = false;
                  this.showThinkingModal = false;
                  this.queryRetryCount = 0; // Reset for future interactions
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
              this.queryRetryCount = 0; // Reset for future interactions
            }
          });
        } else {
          console.error('Error: No SQL query generated for:', naturalLanguageQuery);
          if (this.queryRetryCount < this.MAX_QUERY_RETRIES) {
            this.queryRetryCount++;
            console.warn(`Query generation retry attempt ${this.queryRetryCount}/${this.MAX_QUERY_RETRIES}`);
            this.execution_context.push(`No SQL query was generated. Please generate a valid SQL query.`);
            this.processQueryStep(naturalLanguageQuery); // Re-trigger query generation
          } else {
            this.messages.push({
              sender: 'ai',
              content: `Error: Failed to generate a SQL query after ${this.MAX_QUERY_RETRIES} attempts. Please refine your request.`
            });
            this.isLoading = false;
            this.showThinkingModal = false;
            this.queryRetryCount = 0; // Reset for future interactions
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
        this.queryRetryCount = 0; // Reset for future interactions
      }
    });
  }


} 