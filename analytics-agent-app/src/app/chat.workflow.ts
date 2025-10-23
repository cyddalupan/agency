
import { lastValueFrom } from 'rxjs';

// Define the interfaces for the services that the orchestrator will depend on.
interface IHistoryService {
  save(item: any): void;
  getHistory(): any[];
}

interface IApiService {
  getAiResponse(context: any[], message: string, role: string): any;
  executeQuery(query: string): any;
}

interface AiResponse {
  content: string;
}

// --- Workflow Orchestrator ---

export class ChatWorkflowOrchestrator {
    private execution_context: any[] = [];

    constructor(
        private historyService: IHistoryService,
        private apiService: IApiService
    ) {}

    async run(prompt: string): Promise<string> {
        try {
            // 1. Collaboration AI
            const collabContent = await this.runCollaboration(prompt);
            this.historyService.save({ author: 'user', content: prompt });

            // Check for the trigger to start the execution phase
            if (!collabContent.includes('[[COLLAB_DONE]]')) {
                this.historyService.save({ author: 'ai', content: collabContent });
                return collabContent;
            }

            // 2. Analysis AI
            const analysisContent = await this.runAnalysis();
            this.execution_context.push({ analysis: analysisContent });

            // 3. Breakdown AI
            const breakdownSteps = await this.runBreakdown(analysisContent);

            // 4. Execution AI (Orchestrator Loop)
            for (const step of breakdownSteps) {
                if (step.toLowerCase().includes('query')) {
                    const queryResult = await this.handleQueryStep(step);
                    if (queryResult.error) {
                        this.execution_context.push({ finalError: `Query failed: ${queryResult.error}` });
                        break; // Halt on query failure
                    }
                    this.execution_context.push({ queryResult });
                } else {
                    // For non-query steps, we can add them to context or handle them as needed.
                    this.execution_context.push({ nonQueryStep: step });
                }
            }

        } catch (error: any) {
            // If any AI step fails, add the error to the context for finalization.
            this.execution_context.push({ majorError: error.message });
        }

        // 8. Finalization AI
        const finalText = await this.runFinalization();

        // 9. HTML Conversion AI
        const finalHtml = await this.runHtmlConversion(finalText);

        // 10. Save to History
        this.historyService.save({ author: 'ai', content: finalHtml });

        return finalHtml;
    }

    private async runCollaboration(prompt: string): Promise<string> {
        const response = await lastValueFrom(this.apiService.getAiResponse(this.historyService.getHistory(), prompt, 'collaborate')) as AiResponse;
        return response.content;
    }

    private async runAnalysis(): Promise<string> {
        const response = await lastValueFrom(this.apiService.getAiResponse(this.historyService.getHistory(), '', 'analyze')) as AiResponse;
        return response.content;
    }

    private async runBreakdown(analysisContent: string): Promise<string[]> {
        const response = await lastValueFrom(this.apiService.getAiResponse([], analysisContent, 'breakdown')) as AiResponse;
        try {
            // The AI is expected to return a JSON array of strings.
            return JSON.parse(response.content);
        } catch (e) {
            console.error("Failed to parse breakdown steps from AI");
            return []; // Return empty steps on failure
        }
    }

    private async handleQueryStep(step: string): Promise<any> {
        const maxRetries = 5;
        let lastResult: any = {};

        for (let attempt = 0; attempt < maxRetries; attempt++) {
            // 5. Query Generation AI
            const query = await this.runQueryGeneration(step);

            // 6. Safety AI (internal check)
            if (!this.isQuerySafe(query)) {
                lastResult = { error: 'Unsafe query detected.' };
                this.execution_context.push({ error: lastResult.error, attempt: attempt + 1 });
                continue; // Try to generate a new, safe query
            }

            // 7. Query Executor
            lastResult = await lastValueFrom(this.apiService.executeQuery(query));
            if (lastResult.error) {
                this.execution_context.push({ error: lastResult.error, attempt: attempt + 1 });
            } else {
                return lastResult; // Success
            }
        }
        return lastResult; // Return final error after retries
    }

    private async runQueryGeneration(step: string): Promise<string> {
        const response = await lastValueFrom(this.apiService.getAiResponse(this.execution_context, step, 'query_generation')) as AiResponse;
        return response.content;
    }

    private isQuerySafe(query: string): boolean {
        const lowerCaseQuery = query.toLowerCase();
        const isUnsafe = lowerCaseQuery.includes('drop') || lowerCaseQuery.includes('delete') || lowerCaseQuery.includes('update');
        return !isUnsafe;
    }

    private async runFinalization(): Promise<string> {
        const response = await lastValueFrom(this.apiService.getAiResponse(this.execution_context, '', 'finalization')) as AiResponse;
        return response.content;
    }

    private async runHtmlConversion(text: string): Promise<string> {
        const response = await lastValueFrom(this.apiService.getAiResponse([], text, 'html_conversion')) as AiResponse;
        return response.content;
    }
}
