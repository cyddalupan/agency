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
  safetyRetryCount: number = 0;
}

describe('ChatOrchestratorService: Breakdown AI', () => {
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

    // Common setup for execution phase tests
    service.newMessage = 'Test message';
    mockApiService.getAiResponse.withArgs(jasmine.any(Array), 'Test message', 'collaborate').and.returnValue(of({ choices: [{ message: { content: 'AI response [[COLLAB_DONE]]' } }] }).pipe(delay(1)));
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
});
