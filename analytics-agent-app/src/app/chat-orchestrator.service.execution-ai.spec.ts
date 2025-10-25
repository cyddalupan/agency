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

describe('ChatOrchestratorService: Execution AI', () => {
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

  describe('[4] Execution AI', () => {
    beforeEach(fakeAsync(() => {
      // Only set up Collaboration and Analysis, and inject an empty breakdown to prevent auto-execution
      service.newMessage = 'Test message for Execution AI';
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), 'Test message for Execution AI', 'collaborate').and.returnValue(of({ choices: [{ message: { content: 'AI response [[COLLAB_DONE]]' } }] }).pipe(delay(1)));
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: 'Analysis output for Execution AI' } }] }).pipe(delay(1)));
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValue(of({ choices: [{ message: { content: '[]' } }] }).pipe(delay(1))); // Empty breakdown

      service.sendMessage();
      tick(1); // collaborate response
      tick(1); // analyze response
      tick(1); // breakdown response (sets breakdownSteps to empty array)

      // At this point, service.breakdownSteps is [], and executeNextStep(0) has NOT been called because handleBreakdownResponse has not received steps.
    }));

    it('should trigger query processing when a step contains [[QUERY_REQUIRED]]', fakeAsync(() => {
      service.breakdownSteps = ['Step 1: Query'];
      service.execution_context = ['Analysis output for Execution AI']; // Re-initialize context for this test
      service.currentAiRole = 'execution';

      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'execution').and.returnValue(of({ choices: [{ message: { content: '[[QUERY_REQUIRED]] Find all active users' } }] }).pipe(delay(1)));
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'query_generation').and.returnValue(of({ choices: [{ message: { content: 'SELECT * FROM users WHERE active = 1;' } }] }).pipe(delay(1)));
      mockApiService.executeQuery.and.returnValue(of({ data: [{ id: 1 }] }).pipe(delay(1)));
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'finalization').and.returnValue(of({ choices: [{ message: { content: 'Finalized Query output' } }] }).pipe(delay(1)));
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'html_conversion').and.returnValue(of({ choices: [{ message: { content: '<p>HTML output</p>' } }] }).pipe(delay(1)));

      (service as any).stateMachine.next({ role: 'execution' });
      tick(1); // execution response

      expect(service.currentAiRole).toBe('query_generation');
      expect(service.thinkingMessage).toBe('Generating SQL query...');
      expect(service.execution_context.slice(-1)[0]).toBe('[[QUERY_REQUIRED]] Find all active users');

      tick(1); // query_generation response
      expect(service.execution_context).toContain('Generated SQL: SELECT * FROM users WHERE active = 1;');
      // Safety check is synchronous, so we immediately move to executing the query
      expect(service.execution_context).toContain('Safety Check: [[SAFE_TO_RUN]]');
      expect(service.thinkingMessage).toBe('Executing SQL query...');

      tick(1); // executeQuery response
      expect(service.execution_context.slice(-1)[0]).toBe('Query Result: {"data":[{"id":1}]}');
      expect(service.currentAiRole).toBe('finalization'); // Should move to finalization after last step
      expect(service.thinkingMessage).toBe('Finalizing the response...');

      tick(1); // finalization response
      expect(service.currentAiRole).toBe('html_conversion');
      expect(service.thinkingMessage).toBe('Converting to HTML...');

      tick(1); // html_conversion response
      expect(service.isLoading).toBe(false);
      expect(service.showThinkingModal).toBe(false);
      expect(mockApiService.saveChatHistory).toHaveBeenCalled();
    }));

    it('should advance to the next step when a step contains [[STEP_COMPLETE]]', fakeAsync(() => {
      service.breakdownSteps = ['Step 1: Complete', 'Step 2: Final Step'];
      service.execution_context = ['Analysis output for Execution AI'];
      service.currentAiRole = 'execution';

      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'execution').and.returnValues(
        of({ choices: [{ message: { content: '[[STEP_COMPLETE]] First step is done.' } }] }).pipe(delay(1)),
        of({ choices: [{ message: { content: '[[STEP_COMPLETE]] Second step is done.' } }] }).pipe(delay(1))
      );
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'finalization').and.returnValue(of({ choices: [{ message: { content: 'Finalized output' } }] }).pipe(delay(1)));
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'html_conversion').and.returnValue(of({ choices: [{ message: { content: '<p>HTML output</p>' } }] }).pipe(delay(1)));

      (service as any).stateMachine.next({ role: 'execution' });
      tick(1); // execution Step 1

      expect(service.thinkingMessage).toContain('Executing step 2/2: Step 2: Final Step');
      expect(service.execution_context.slice(-1)[0]).toBe('[[STEP_COMPLETE]] First step is done.');

      tick(1); // execution Step 2

      expect(service.currentAiRole).toBe('finalization');
      expect(service.thinkingMessage).toBe('Finalizing the response...');

      tick(1); // finalization response
      expect(service.currentAiRole).toBe('html_conversion');
      tick(1); // html_conversion response
      expect(service.isLoading).toBe(false);
      expect(service.showThinkingModal).toBe(false);
    }));

    it('should advance to the next step when a step response has no trigger', fakeAsync(() => {
      service.breakdownSteps = ['Step 1: No Trigger', 'Step 2: Final Step'];
      service.execution_context = ['Analysis output for Execution AI'];
      service.currentAiRole = 'execution';

      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'execution').and.returnValues(
        of({ choices: [{ message: { content: 'Internal action performed for step 1.' } }] }).pipe(delay(1)),
        of({ choices: [{ message: { content: 'Internal action performed for step 2.' } }] }).pipe(delay(1))
      );
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'finalization').and.returnValue(of({ choices: [{ message: { content: 'Finalized output' } }] }).pipe(delay(1)));

      (service as any).stateMachine.next({ role: 'execution' });
      tick(1); // execution Step 1

      expect(service.thinkingMessage).toContain('Executing step 2/2: Step 2: Final Step');
      expect(service.execution_context.slice(-1)[0]).toBe('Internal action performed for step 1.');

      tick(1); // execution Step 2

      expect(service.currentAiRole).toBe('finalization');
      expect(service.thinkingMessage).toBe('Finalizing the response...');

      tick(1); // finalization response
      expect(service.currentAiRole).toBe('html_conversion');
      tick(1); // html_conversion response
      expect(service.isLoading).toBe(false);
      expect(service.showThinkingModal).toBe(false);
    }));

    it('should handle API error during execution and stop the process', fakeAsync(() => {
      service.breakdownSteps = ['Step 1: Error'];
      service.execution_context = ['Analysis output for Execution AI'];
      service.currentAiRole = 'execution';
      const errorResponse = { status: 500, statusText: 'Internal Server Error' };

      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'execution').and.returnValue(throwError(() => errorResponse).pipe(delay(1)));

      (service as any).stateMachine.next({ role: 'execution' });
      tick(1); // execution Step 1 (error)

      expect(service.isLoading).toBe(false);
      expect(service.showThinkingModal).toBe(false);
      expect(service.messages.slice(-1)[0].content).toContain('Error: Could not get a response from the Execution AI for step "Step 1: Error".');
    }));

    it('should handle empty response from Execution AI and stop the process', fakeAsync(() => {
      service.breakdownSteps = ['Step 1: Empty'];
      service.execution_context = ['Analysis output for Execution AI'];
      service.currentAiRole = 'execution';

      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'execution').and.returnValue(of({ choices: [{ message: { content: null } }] }).pipe(delay(1)));
      
      (service as any).stateMachine.next({ role: 'execution' });
      tick(1); // execution Step 1 (empty response)

      expect(service.isLoading).toBe(false);
      expect(service.showThinkingModal).toBe(false);
      expect(service.messages.slice(-1)[0].content).toContain('Error: No response from Execution AI for step "Step 1: Empty".');
    }));
  });
});
