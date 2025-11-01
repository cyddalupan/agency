import { TestBed, fakeAsync, tick } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { of } from 'rxjs';
import { delay } from 'rxjs/operators';

import { ChatOrchestratorService } from './chat-orchestrator.service';
import { ApiService } from './api';

class MockApiService {
  getChatHistory = jasmine.createSpy('getChatHistory').and.returnValue(of({ data: [] }));
  saveChatHistory = jasmine.createSpy('saveChatHistory').and.returnValue(of({}));
  getAiResponse = jasmine.createSpy('getAiResponse').and.returnValue(of({ choices: [{ message: { content: 'AI response' } }] }));
  executeQuery = jasmine.createSpy('executeQuery').and.returnValue(of({}));
  safetyRetryCount: number = 0;
}

describe('ChatOrchestratorService: Integration', () => {
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
    expect(service.thinkingMessage).toBe('Final Cleanups...');
    tick(1); // Allow html_conversion call to resolve

    // --- HTML Conversion Phase ---
    expect(service.messages.length).toBe(initialMessageCount + 2); // user message + final AI message
    expect(service.messages[initialMessageCount + 1].content).toBe('HTML Conversion AI output');
    expect(service.isLoading).toBe(false);
    expect(service.showThinkingModal).toBe(false);
    expect(mockApiService.saveChatHistory).toHaveBeenCalledWith('Test message with trigger', 'HTML Conversion AI output');
  }));
});
