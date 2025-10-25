import { TestBed, fakeAsync, tick } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { of, throwError } from 'rxjs';
import { delay } from 'rxjs/operators';

import { ChatOrchestratorService } from './chat-orchestrator.service';
import { ApiService } from './api';

class MockApiService {
  getChatHistory = jasmine.createSpy('getChatHistory').and.returnValue(of({ data: [] }));
  saveChatHistory = jasmine.createSpy('saveChatHistory').and.returnValue(of({}));
  getAiResponse = jasmine.createSpy('getAiResponse').and.returnValue(of({ choices: [{ message: { content: 'AI response' } }] }));
  executeQuery = jasmine.createSpy('executeQuery').and.returnValue(of({}));
}

describe('ChatOrchestratorService', () => {
  let service: ChatOrchestratorService;
  let mockApiService: MockApiService;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [
        ChatOrchestratorService,
        { provide: ApiService, useClass: MockApiService }
      ]
    });
    service = TestBed.inject(ChatOrchestratorService);
    mockApiService = TestBed.inject(ApiService) as unknown as MockApiService;
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  describe('Execution Phase', () => {
    beforeEach(() => {
      service.newMessage = 'Test message';
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), 'Test message', 'collaborate').and.returnValue(of({ choices: [{ message: { content: 'AI response [[COLLAB_DONE]]' } }] }).pipe(delay(1)));
    });

    describe('[2] Analysis AI', () => {
      it('should transition to breakdown phase on successful analysis', fakeAsync(() => {
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: 'Analysis AI output' } }] }).pipe(delay(1)));
        // Mock the next step to prevent further execution
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValue(of({ choices: [{ message: { content: '[]' } }] }).pipe(delay(1)));

        service.sendMessage();
        tick(1); // Resolve collaboration

        expect(service.currentAiRole).toBe('analyze');
        expect(service.thinkingMessage).toBe('Analyzing request...');

        tick(1); // Resolve analysis

        expect(service.execution_context).toEqual(['Analysis AI output']);
        expect(service.currentAiRole).toBe('breakdown');
        expect(service.thinkingMessage).toBe('Breaking down the task into steps...');
      }));

      it('should handle API error during analysis', fakeAsync(() => {
        const errorResponse = { status: 500, statusText: 'Internal Server Error' };
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(throwError(() => errorResponse).pipe(delay(1)));

        service.sendMessage();
        tick(1); // Resolve collaboration

        expect(service.currentAiRole).toBe('analyze');
        expect(service.thinkingMessage).toBe('Analyzing request...');

        tick(1); // Resolve analysis (error)

        expect(service.messages.slice(-1)[0].content).toBe('Error: Could not get a response from the Analysis AI.');
        expect(service.isLoading).toBe(false);
        expect(service.showThinkingModal).toBe(false);
      }));

      it('should handle empty or invalid response from Analysis AI', fakeAsync(() => {
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: null } }] }).pipe(delay(1)));
        // Mock the next step to prevent further execution
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValue(of({ choices: [{ message: { content: '[]' } }] }).pipe(delay(1)));

        service.sendMessage();
        tick(1); // Resolve collaboration
        tick(1); // Resolve analysis

        expect(service.execution_context).toEqual(['No response from Analysis AI.']);
        expect(service.currentAiRole).toBe('breakdown');
      }));
    });

    describe('[3] Breakdown AI', () => {

      it('should parse valid JSON array and proceed to execution', fakeAsync(() => {
        const validSteps = '["Step 1", "Step 2"]';
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: 'Analysis AI output' } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValue(of({ choices: [{ message: { content: validSteps } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'execution').and.returnValue(of({ choices: [{ message: { content: '[[STEP_COMPLETE]]' } }] }).pipe(delay(1))
        );

        service.sendMessage();
        tick(1); // collaboration
        tick(1); // analysis
        tick(1); // breakdown

        expect(service.breakdownSteps).toEqual(['Step 1', 'Step 2']);
        expect(service.breakdownRetryCount).toBe(0);
      }));

      it('should handle API error during breakdown', fakeAsync(() => {
        const errorResponse = { status: 500, statusText: 'Internal Server Error' };
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: 'Analysis AI output' } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValue(throwError(() => errorResponse).pipe(delay(1)));

        service.sendMessage();
        tick(1); // collaboration
        tick(1); // analysis
        tick(1); // breakdown (error)

        expect(service.messages.slice(-1)[0].content).toBe('Error: Could not get a response from the Breakdown AI.');
        expect(service.isLoading).toBe(false);
        expect(service.showThinkingModal).toBe(false);
      }));

      it('should handle empty or null response from Breakdown AI', fakeAsync(() => {
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: 'Analysis AI output' } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValue(of({ choices: [{ message: { content: null } }] }).pipe(delay(1)));

        service.sendMessage();
        tick(1); // collaboration
        tick(1); // analysis
        tick(1); // breakdown

        expect(service.messages.slice(-1)[0].content).toBe('Error: No response from Breakdown AI.');
        expect(service.isLoading).toBe(false);
      }));

      it('should retry on invalid JSON and succeed on the second attempt', fakeAsync(() => {
        const invalidJson = 'This is not JSON';
        const validJson = '["Step A", "Step B"]';

        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: 'Analysis AI output' } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValues(
          of({ choices: [{ message: { content: invalidJson } }] }).pipe(delay(1)),
          of({ choices: [{ message: { content: validJson } }] }).pipe(delay(1))
        );
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'execution').and.returnValue(of({ choices: [{ message: { content: '[[STEP_COMPLETE]]' } }] }).pipe(delay(1)));

        service.sendMessage();
        tick(1); // collaboration
        tick(1); // analysis
        tick(1); // first breakdown

        expect(service.breakdownRetryCount).toBe(1);

        tick(1); // second breakdown

        expect(service.breakdownSteps).toEqual(['Step A', 'Step B']);
        expect(service.breakdownRetryCount).toBe(0);
      }));

      it('should fail after max retries on persistent invalid JSON', fakeAsync(() => {
        const invalidJson = 'Persistent invalid JSON';

        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: 'Analysis AI output' } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValue(of({ choices: [{ message: { content: invalidJson } }] }).pipe(delay(1)));

        service.sendMessage();
        tick(1); // collaboration
        tick(1); // analysis
        tick(1); // breakdown 1
        tick(1); // breakdown 2
        tick(1); // breakdown 3

        expect(service.messages.slice(-1)[0].content).toBe('Error: Breakdown AI failed to produce valid steps after multiple attempts.');
        expect(service.isLoading).toBe(false);
        expect(service.showThinkingModal).toBe(false);
        expect(service.breakdownRetryCount).toBe(0); // Resets on final failure
      }));

      it('should retry on valid JSON but invalid format (not an array of strings)', fakeAsync(() => {
        const invalidFormat = '{"steps": "not an array"}'; // Valid JSON, but not string[]
        const validFormat = '["Correct Step"]';

        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: 'Analysis AI output' } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValues(
          of({ choices: [{ message: { content: invalidFormat } }] }).pipe(delay(1)),
          of({ choices: [{ message: { content: validFormat } }] }).pipe(delay(1))
        );
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'execution').and.returnValue(of({ choices: [{ message: { content: '[[STEP_COMPLETE]]' } }] }).pipe(delay(1)));

        service.sendMessage();
        tick(1); // collaboration
        tick(1); // analysis
        tick(1); // first breakdown

        expect(service.breakdownRetryCount).toBe(1);

        tick(1); // second breakdown

        expect(service.breakdownSteps).toEqual(['Correct Step']);
        expect(service.breakdownRetryCount).toBe(0);
      }));
    });

    it('should handle COLLAB_DONE trigger and transition through all AI roles (full flow)', fakeAsync(() => {
        const initialMessageCount = service.messages.length;
        service.newMessage = 'Test message with trigger';
    
        // Mock the responses for each AI role
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), 'Test message with trigger', 'collaborate').and.returnValue(of({ choices: [{ message: { content: 'AI response [[COLLAB_DONE]]' } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: 'Analysis AI output' } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValue(of({ choices: [{ message: { content: '["step 1", "step 2"]' } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'execution').and.returnValues(
          of({ choices: [{ message: { content: '[[STEP_COMPLETE]]' } }] }).pipe(delay(1)), // Step 1
          of({ choices: [{ message: { content: '[[STEP_COMPLETE]]' } }] }).pipe(delay(1))  // Step 2
        );
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'finalization').and.returnValue(of({ choices: [{ message: { content: 'Finalization AI output' } }] }).pipe(delay(1)));
        mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'html_conversion').and.returnValue(of({ choices: [{ message: { content: 'HTML Conversion AI output' } }] }).pipe(delay(1)));
    
    
        service.sendMessage();
        tick(1); // Allow collaborate call to resolve
    
        // --- Collaborate Phase ---
        expect(service.currentAiRole).toBe('analyze');
        expect(service.thinkingMessage).toBe('Analyzing request...');
        tick(1); // Allow analyze call to resolve
    
        // --- Analyze Phase ---
        expect(service.execution_context).toEqual(['Analysis AI output']);
        expect(service.currentAiRole).toBe('breakdown');
        expect(service.thinkingMessage).toBe('Breaking down the task into steps...');
        tick(1); // Allow breakdown call to resolve
    
        // --- Breakdown Phase ---
        expect(service.breakdownSteps).toEqual(['step 1', 'step 2']);
        expect(service.currentAiRole).toBe('execution');
        expect(service.thinkingMessage).toContain('Executing step 1/2: step 1');
        tick(1); // Allow execution step 1 to resolve
    
        // --- Execution Phase (Step 1) ---
        expect(service.thinkingMessage).toContain('Executing step 2/2: step 2');
        tick(1); // Allow execution step 2 to resolve
    
        // --- Execution Phase (Step 2) ---
        expect(service.currentAiRole).toBe('finalization');
        expect(service.thinkingMessage).toBe('Finalizing the response...');
        tick(1); // Allow finalization call to resolve
    
        // --- Finalization Phase ---
        expect(service.currentAiRole).toBe('html_conversion');
        expect(service.thinkingMessage).toBe('Converting to HTML...');
        tick(1); // Allow html_conversion call to resolve
    
        // --- HTML Conversion Phase ---
        expect(service.messages.length).toBe(initialMessageCount + 2); // user message + final AI message
        expect(service.messages[initialMessageCount + 1].content).toBe('HTML Conversion AI output');
        expect(service.isLoading).toBe(false);
        expect(service.showThinkingModal).toBe(false);
        expect(mockApiService.saveChatHistory).toHaveBeenCalledWith('Test message with trigger', 'HTML Conversion AI output');
      }));
  });
});