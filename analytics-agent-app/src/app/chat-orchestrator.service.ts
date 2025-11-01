import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { ApiService } from './api';
import { APPLICANT_TABLE_SCHEMA } from './schemas';
import {
  BREAKDOWN_PROMPT_INSTRUCTIONS,
  BREAKDOWN_PROMPT_GOOD_EXAMPLE,
  BREAKDOWN_PROMPT_BAD_EXAMPLE,
  BREAKDOWN_PROMPT_SCHEMA_CONTEXT
} from './prompts';

interface Message {
  sender: 'user' | 'ai';
  content: string;
}

interface State {
  role: string;
  data?: any;
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
  public queryRetryCount: number = 0;
  public safetyRetryCount: number = 0;
  private readonly MAX_QUERY_RETRIES: number = 5;
  private currentUserMessageForHistory: string = '';
  private aiInteractionCount = 0;
  private readonly MAX_AI_INTERACTIONS = 25;

  private stateMachine = new Subject<State>();

  constructor(private apiService: ApiService) {
    this.stateMachine.subscribe(state => {
      this.currentAiRole = state.role;
      switch (state.role) {
        case 'collaborate':
          this.handleCollaborate(state.data);
          break;
        case 'analyze':
          this.handleAnalyze();
          break;
        case 'breakdown':
          this.handleBreakdown(state.data);
          break;
        case 'execution':
          this.handleExecution();
          break;
        case 'query_generation':
          this.handleQueryGeneration(state.data);
          break;
        case 'safety_check':
          this.handleSafetyCheck(state.data);
          break;
        case 'finalization':
          this.handleFinalization();
          break;
        case 'html_conversion':
          this.handleHtmlConversion(state.data);
          break;
      }
    });
  }

  public loadChatHistory(): void {
    const query = 'SELECT message, reply FROM chat_history ORDER BY timestamp ASC LIMIT 20';
    this.apiService.executeQuery(query, []).subscribe({
      next: (response: any) => {
        this.messages = [];
        if (response && response.data) {
          response.data.forEach((record: { message: string; reply: string; }) => {
            if (record.message) {
              this.messages.push({ sender: 'user', content: record.message });
            }
            if (record.reply) {
              this.messages.push({ sender: 'ai', content: record.reply });
            }
          });
        }
      },
      error: (err) => {
        console.error('Error loading chat history:', err);
      }
    });
  }

  private cleanAiContent(content: string): string {
    return content.replace(/\s*\[[.*?]\s*/g, ' ').trim();
  }

  private getAiRolePrompt(): string {
    const dbSchema = APPLICANT_TABLE_SCHEMA;
    switch (this.currentAiRole) {
      case 'collaborate':
        return `You are a Collaboration AI for a deployment agency system. Your purpose is to act as a helpful assistant, clarifying the user\'s needs to generate a precise context for subsequent AI agents. Reply in short, easy-to-understand messages and avoid technical terms. Your goal is to fully understand what the user wants to achieve. Crucially, you must not ask about or discuss the final output format (e.g., CSV, JSON, HTML). The system handles all formatting automatically. Your sole focus is to understand the user\'s goal. When you have a clear understanding and a detailed context, output the trigger [[COLLAB_DONE]]..\n\nAvailable Database Schema for your reference:\n${dbSchema}`;
      case 'analyze':
        return `You are an Analysis AI. Your task is to summarize the user\'s intent and the clarified context from the Collaboration AI into a concise brief for the Breakdown AI. You will receive a structured context object. focus on the newer message from user. \n\n        Your output MUST be a detailed description of what the user needs.\n\n        Available Database Schema:\n        ${dbSchema}`;
      case 'breakdown':
        return `${BREAKDOWN_PROMPT_INSTRUCTIONS}
${BREAKDOWN_PROMPT_GOOD_EXAMPLE}
${BREAKDOWN_PROMPT_BAD_EXAMPLE}
${BREAKDOWN_PROMPT_SCHEMA_CONTEXT}

${dbSchema}`;
      case 'execution':
        return `You are an Execution AI. Your task is to process a single step from a breakdown plan. Determine if the step requires a database query. If it does, output [[QUERY_REQUIRED]] followed by a natural language description of the query needed. If it does not, output [[STEP_COMPLETE]] followed by a confirmation or the result of the internal action. The database schema is provided for context:\n\n${APPLICANT_TABLE_SCHEMA}`;
      case 'query_generation':
        return `You are a Query Generation AI. Your task is to convert a natural language query description into a valid MySQL query (MySQL 5.7.23 meaning use example: applicant_id = '51' not applicant_id = 51). Use the provided database schema for reference. Output ONLY the SQL query string. Do not include any other text or conversational filler. If you cannot generate a valid SQL query, output an empty string or an error message. The database schema is provided for context:\n\n${APPLICANT_TABLE_SCHEMA}`;
      case 'safety_check':
        return `You are a Safety AI. Your task is to analyze a given SQL query for specific structural risks. Your goal is to prevent database damage. A query is considered **UNSAFE** only if it contains commands that alter the database schema (e.g., <table>DROP TABLE</table>, <table>ALTER TABLE</table>, <table>TRUNCATE TABLE</table>) or contains high-risk patterns like <table>UNION ALL</table>, <table>LOAD DATA INFILE</table>, or SQL comments (<table>--</table>, <table>#</table>). A query is considered **SAFE** if it is a standard <table>SELECT</table>, <table>INSERT</table>, <table>UPDATE</table>, or <table>DELETE</table> statement used for data retrieval or modification. Accessing personal or sensitive data is **not** an unsafe condition. Respond with [[SAFE_TO_RUN]] if the query is safe. If it is unsafe, respond with [[UNSAFE: <reason>]] and specify the dangerous pattern found.`;
      case 'finalization':
        return `You are a Finalization AI. Your task is to aggregate all results and produce a final, user-facing summary or answer. Synthesize the information into a coherent and human-readable response. focus on the value non technical user can understand. do not include the code and sql queries`;
      case 'html_conversion':
        return `You are an HTML Conversion AI. Your task is to convert the final AI response to HTML (mobile size) using Tailwind CSS for styling (which is already installed). Make sure to use a generous amount of Font Awesome icons to enhance the visual presentation. avoid gray font because the parent html background is darkgray unless we add a customized background color. This is a view only html avoid using form attributes. Do not include any other text or conversational filler.`;
      default:
        return `You are a helpful AI assistant.`;
    }
  }

  sendMessage(): void {
    if (this.newMessage.trim() === '') {
      return;
    }

    this.aiInteractionCount = 0;
    this.messages.push({
      sender: 'user',
      content: this.newMessage
    });

    const userMessage = this.newMessage;
    this.currentUserMessageForHistory = userMessage;
    this.newMessage = '';

    this.isLoading = true;
    this.thinkingMessage = 'Thinking...';

    this.stateMachine.next({ role: 'collaborate', data: userMessage });
  }

  private handleCollaborate(userMessage: string): void {
    this.aiInteractionCount++;
    if (this.aiInteractionCount > this.MAX_AI_INTERACTIONS) {
      this.handleFatalError('Error: Maximum AI interactions exceeded for this request.');
      return;
    }

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
          this.stateMachine.next({ role: 'analyze' });
        } else {
          displayContent = this.cleanAiContent(displayContent);
          this.messages.push({ sender: 'ai', content: displayContent });
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
        this.messages.push({ sender: 'ai', content: 'Error: Could not get a response from the AI.' });
        this.isLoading = false;
        this.showThinkingModal = false;
      }
    });
  }

  private handleAnalyze(): void {
    this.aiInteractionCount++;
    if (this.aiInteractionCount > this.MAX_AI_INTERACTIONS) {
      this.handleFatalError('Error: Maximum AI interactions exceeded for this request.');
      return;
    }

    this.thinkingMessage = 'Analyzing request...';
    const analysisPrompt = this.getAiRolePrompt();
    const analysisContextMessages = this.messages.slice(-10).map(msg => ({
      role: msg.sender === 'user' ? 'user' : 'assistant',
      content: msg.content
    }));
    analysisContextMessages.unshift({ role: 'system', content: analysisPrompt });

    this.apiService.getAiResponse(analysisContextMessages, '', this.currentAiRole).subscribe({
      next: (analysisResponse: any) => {
        let rawAnalysisContent = analysisResponse.choices?.[0]?.message?.content;
        let displayAnalysisContent = rawAnalysisContent || 'No response from Analysis AI.';
        this.execution_context.push(displayAnalysisContent);
        this.stateMachine.next({ role: 'breakdown' });
      },
      error: (analysisError: any) => {
        console.error('Error fetching Analysis AI response:', analysisError);
        this.messages.push({ sender: 'ai', content: 'Error: Could not get a response from the Analysis AI.' });
        this.isLoading = false;
        this.showThinkingModal = false;
      }
    });
  }

  private handleBreakdown(data?: any): void {
    this.aiInteractionCount++;
    if (this.aiInteractionCount > this.MAX_AI_INTERACTIONS) {
      this.handleFatalError('Error: Maximum AI interactions exceeded for this request.');
      return;
    }

    this.thinkingMessage = 'Breaking down the task into steps...';
    const breakdownPrompt = this.getAiRolePrompt();
    const analysisOutputForBreakdown = this.execution_context[this.execution_context.length - 1];
    const breakdownContextMessages = [
      { role: 'system', content: breakdownPrompt },
      { role: 'user', content: analysisOutputForBreakdown }
    ];

    if (data?.retry) {
      breakdownContextMessages.push({ role: 'assistant', content: data.rawContent });
      breakdownContextMessages.push({ role: 'user', content: `Previous response was not a valid JSON array of strings. Please provide the breakdown again in the correct format: ["step 1", "step 2"].` });
    }

    this.apiService.getAiResponse(breakdownContextMessages, '', this.currentAiRole).subscribe({
      next: (res) => {
        let rawBreakdownContent = res.choices?.[0]?.message?.content;
        if (rawBreakdownContent) {
          try {
            const parsedSteps = JSON.parse(rawBreakdownContent);
            if (Array.isArray(parsedSteps) && parsedSteps.every(step => typeof step === 'string')) {
              this.breakdownSteps = parsedSteps;
              this.breakdownRetryCount = 0;
              this.stateMachine.next({ role: 'execution' });
            } else {
              throw new Error('Parsed content is not a JSON array of strings.');
            }
          } catch (e) {
            console.error('Error parsing Breakdown AI response or invalid format:', e);
            if (this.breakdownRetryCount < this.MAX_BREAKDOWN_RETRIES) {
              this.breakdownRetryCount++;
              console.warn(`Breakdown AI retry attempt ${this.breakdownRetryCount}/${this.MAX_BREAKDOWN_RETRIES}`);
              this.stateMachine.next({ role: 'breakdown', data: { retry: true, rawContent: rawBreakdownContent } });
            } else {
              this.messages.push({ sender: 'ai', content: 'Error: Breakdown AI failed to produce valid steps after multiple attempts.' });
              this.isLoading = false;
              this.showThinkingModal = false;
              this.breakdownRetryCount = 0;
            }
          }
        } else {
          this.messages.push({ sender: 'ai', content: 'Error: No response from Breakdown AI.' });
          this.isLoading = false;
          this.showThinkingModal = false;
          this.breakdownRetryCount = 0;
        }
      },
      error: (err) => {
        console.error('Error fetching Breakdown AI response:', err);
        this.messages.push({ sender: 'ai', content: 'Error: Could not get a response from the Breakdown AI.' });
        this.isLoading = false;
        this.showThinkingModal = false;
        this.breakdownRetryCount = 0;
      }
    });
  }

  private handleExecution(): void {
    this.aiInteractionCount++;
    if (this.aiInteractionCount > this.MAX_AI_INTERACTIONS) {
      this.handleFatalError('Error: Maximum AI interactions exceeded for this request.');
      return;
    }

    if (this.currentStepIndex >= this.breakdownSteps.length) {
      this.stateMachine.next({ role: 'finalization' });
      return;
    }

    const currentStep = this.breakdownSteps[this.currentStepIndex];
    this.thinkingMessage = `Executing step ${this.currentStepIndex + 1}/${this.breakdownSteps.length}: ${currentStep}`;

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

          if (rawExecutionContent.includes('[[QUERY_REQUIRED]]')) {
            const naturalLanguageQuery = rawExecutionContent.replace('[[QUERY_REQUIRED]]', '').trim();
            this.stateMachine.next({ role: 'query_generation', data: naturalLanguageQuery });
          } else {
            this.currentStepIndex++;
            this.stateMachine.next({ role: 'execution' });
          }
        } else {
          console.error('Error: No response from Execution AI for step:', currentStep);
          this.messages.push({ sender: 'ai', content: `Error: No response from Execution AI for step "${currentStep}".` });
          this.isLoading = false;
          this.showThinkingModal = false;
        }
      },
      error: (executionError: any) => {
        console.error('Error fetching Execution AI response:', executionError);
        this.messages.push({ sender: 'ai', content: `Error: Could not get a response from the Execution AI for step "${currentStep}".` });
        this.isLoading = false;
        this.showThinkingModal = false;
      }
    });
  }

  private handleQueryGeneration(naturalLanguageQuery: string): void {
    this.aiInteractionCount++;
    if (this.aiInteractionCount > this.MAX_AI_INTERACTIONS) {
      this.handleFatalError('Error: Maximum AI interactions exceeded for this request.');
      return;
    }

    this.thinkingMessage = 'Generating SQL query...';

    const queryGenerationPrompt = this.getAiRolePrompt();
    const queryGenerationContextMessages = [
      { role: 'system', content: queryGenerationPrompt },
      ...this.execution_context.map(content => ({ role: 'assistant', content: content })),
      { role: 'user', content: naturalLanguageQuery }
    ];

    if (this.queryRetryCount > 0) {
      const lastError = this.execution_context[this.execution_context.length - 1];
      queryGenerationContextMessages.push({ role: 'user', content: `The previous attempt to execute the query failed with the following error: \n${lastError}\n. Please analyze the error and the database schema to generate a corrected and valid MySQL query.` });
    }

    this.apiService.getAiResponse(queryGenerationContextMessages, '', this.currentAiRole).subscribe({
      next: (queryResponse: any) => {
        let rawSqlQuery = queryResponse.choices?.[0]?.message?.content;
        if (rawSqlQuery) {
          this.execution_context.push(`Generated SQL: ${rawSqlQuery}`);
          this.stateMachine.next({ role: 'safety_check', data: { query: rawSqlQuery, nlp: naturalLanguageQuery } });
        } else {
          console.error('Error: No SQL query generated for:', naturalLanguageQuery);
          if (this.queryRetryCount < this.MAX_QUERY_RETRIES) {
            this.queryRetryCount++;
            console.warn(`Query generation retry attempt ${this.queryRetryCount}/${this.MAX_QUERY_RETRIES}`);
            this.execution_context.push(`No SQL query was generated. Please generate a valid SQL query.`);
            this.stateMachine.next({ role: 'query_generation', data: naturalLanguageQuery });
          } else {
            this.messages.push({ sender: 'ai', content: `Error: Failed to generate a SQL query after ${this.MAX_QUERY_RETRIES} attempts. Please refine your request.` });
            this.isLoading = false;
            this.showThinkingModal = false;
            this.queryRetryCount = 0;
          }
        }
      },
      error: (queryGenerationError: any) => {
        console.error('Error fetching Query Generation AI response:', queryGenerationError);
        this.messages.push({ sender: 'ai', content: `Error: Could not get a response from the Query Generation AI for "${naturalLanguageQuery}".` });
        this.isLoading = false;
        this.showThinkingModal = false;
        this.queryRetryCount = 0;
      }
    });
  }

  private handleFatalError(errorMessage: string): void {
    this.messages.push({ sender: 'ai', content: errorMessage });
    this.isLoading = false;
    this.showThinkingModal = false;
    this.queryRetryCount = 0;
    this.safetyRetryCount = 0;
    this.breakdownRetryCount = 0;
  }

  private handleSafetyCheck(data: { query: string, nlp: string }): void {
    this.aiInteractionCount++;
    if (this.aiInteractionCount > this.MAX_AI_INTERACTIONS) {
      this.handleFatalError('Error: Maximum AI interactions exceeded for this request.');
      return;
    }

    this.thinkingMessage = 'Performing safety check on query...';
    const safetyPrompt = this.getAiRolePrompt();
    const safetyContextMessages = [
      { role: 'system', content: safetyPrompt },
      ...this.execution_context.map(content => ({ role: 'assistant', content: content }))
    ];

    this.apiService.getAiResponse(safetyContextMessages, data.query, this.currentAiRole).subscribe({
      next: (response: any) => {
        const rawAiContent = response.choices?.[0]?.message?.content;

        if (rawAiContent && rawAiContent.includes('[[SAFE_TO_RUN]]')) {
          this.execution_context.push('Safety Check: [[SAFE_TO_RUN]]');
          this.thinkingMessage = 'Executing SQL query...';
          this.executeQueryWithRetries(data);
        } else {
          let reason = 'AI deemed the query unsafe, but did not provide a specific reason.';
          if (rawAiContent && rawAiContent.startsWith('[[UNSAFE:')) {
            reason = rawAiContent.substring(9, rawAiContent.length - 2);
          }
          
          const unsafeResponse = `[[UNSAFE: ${reason}]]`;
          console.warn(`SQL query failed safety check: ${reason}`);
          this.execution_context.push(`Safety Check: ${unsafeResponse}`);

          if (this.safetyRetryCount < this.MAX_QUERY_RETRIES) {
            this.safetyRetryCount++;
            console.warn(`Query safety retry attempt ${this.safetyRetryCount}/${this.MAX_QUERY_RETRIES}`);
            this.stateMachine.next({ role: 'query_generation', data: data.nlp });
          } else {
            this.handleFatalError(`Error: Failed to generate a safe query after ${this.MAX_QUERY_RETRIES} attempts. Please refine your request.`);
          }
        }
      },
      error: (error: any) => {
        this.handleFatalError('Error: Could not get a response from the Safety AI.');
      }
    });
  }

  private executeQueryWithRetries(data: { query: string, nlp: string }): void {
    this.apiService.executeQuery(data.query, []).subscribe({
      next: (queryResult: any) => {
        this.execution_context.push(`Query Result: ${JSON.stringify(queryResult)}`);
        this.queryRetryCount = 0;
        this.currentStepIndex++;
        this.stateMachine.next({ role: 'execution' });
      },
      error: (queryError: any) => {
        console.error('Error executing SQL query:', queryError);
        this.queryRetryCount++;

        if (this.queryRetryCount < this.MAX_QUERY_RETRIES) {
          console.warn(`Query execution retry attempt ${this.queryRetryCount}/${this.MAX_QUERY_RETRIES}`);

          let backendError = queryError.message; // Default message
          if (queryError.error) {
            if (typeof queryError.error === 'object' && queryError.error.error) {
              backendError = queryError.error.error; // Handles { "error": "..." }
            } else if (typeof queryError.error === 'string') {
              backendError = queryError.error; // Handles raw string error
            }
          }

          this.execution_context.push(`SQL Error: ${backendError}. Please correct the SQL query.`);
          this.stateMachine.next({ role: 'query_generation', data: data.nlp });
        } else {
          this.handleFatalError(`Error: Failed to execute query after ${this.MAX_QUERY_RETRIES} attempts. Please refine your request.`);
        }
      }
    });
  }

  private handleFinalization(): void {
    this.aiInteractionCount++;
    if (this.aiInteractionCount > this.MAX_AI_INTERACTIONS) {
      this.handleFatalError('Error: Maximum AI interactions exceeded for this request.');
      return;
    }

    this.thinkingMessage = 'Finalizing the response...';
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
        this.stateMachine.next({ role: 'html_conversion', data: displayFinalizationContent });
      },
      error: (finalizationError: any) => {
        console.error('Error fetching Finalization AI response:', finalizationError);
        this.messages.push({ sender: 'ai', content: 'Error: Could not get a finalization message from the AI.' });
        this.isLoading = false;
        this.showThinkingModal = false;
      }
    });
  }

  private handleHtmlConversion(contentToConvert: string): void {
    this.aiInteractionCount++;
    if (this.aiInteractionCount > this.MAX_AI_INTERACTIONS) {
      this.handleFatalError('Error: Maximum AI interactions exceeded for this request.');
      return;
    }

    if (!contentToConvert) {
      this.messages.push({ sender: 'ai', content: 'Error: Input to HTML conversion was null or undefined.' });
      this.isLoading = false;
      this.showThinkingModal = false;
      return;
    }
    this.thinkingMessage = 'Final Cleanups...';
    const conversionPrompt = this.getAiRolePrompt();
    const conversionContextMessages = [
      { role: 'system', content: conversionPrompt },
      { role: 'user', content: contentToConvert }
    ];

    this.apiService.getAiResponse(conversionContextMessages, '', this.currentAiRole).subscribe({
      next: (conversionResponse: any) => {
        let rawHtmlContent = conversionResponse.choices?.[0]?.message?.content;
        let displayHtmlContent = rawHtmlContent || 'No HTML response from AI.';
        this.messages.push({ sender: 'ai', content: displayHtmlContent });
        this.isLoading = false;
        this.showThinkingModal = false;
        this.apiService.saveChatHistory(this.currentUserMessageForHistory, displayHtmlContent).subscribe({
          next: (saveResponse) => console.log('HTML content saved:', saveResponse),
          error: (saveError) => console.error('Error saving HTML content:', saveError)
        });
      },
      error: (conversionError: any) => {
        console.error('Error fetching HTML Conversion AI response:', conversionError);
        this.messages.push({ sender: 'ai', content: 'Error: Could not get a response from the HTML Conversion AI.' });
        this.isLoading = false;
        this.showThinkingModal = false;
      }
    });
  }
}
