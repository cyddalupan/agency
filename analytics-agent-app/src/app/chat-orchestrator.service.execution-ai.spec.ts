import { TestBed, fakeAsync, tick } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { of, throwError } from 'rxjs';
import { delay } from 'rxjs/operators';

import { ChatOrchestratorService } from './chat-orchestrator.service';
import { ApiService } from './api';

class MockApiService {
  getAiResponse = jasmine.createSpy('getAiResponse').and.returnValue(of({ choices: [{ message: { content: 'AI response' } }] }));
  executeQuery = jasmine.createSpy('executeQuery').and.returnValue(of({}));
  saveChatHistory = jasmine.createSpy('saveChatHistory').and.returnValue(of({}));
}

describe('ChatOrchestratorService: Execution AI', () => {
  let service: ChatOrchestratorService;
  let apiService: MockApiService;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [
        ChatOrchestratorService,
        { provide: ApiService, useClass: MockApiService }
      ]
    });
    service = TestBed.inject(ChatOrchestratorService);
    apiService = TestBed.inject(ApiService) as unknown as MockApiService;
  });

  describe('[4] Execution AI', () => {
    it('should transition to query_generation when AI returns [[QUERY_REQUIRED]]', fakeAsync(() => {
      service.breakdownSteps = ['Step 1'];
      (service as any).currentStepIndex = 0;
      service.currentAiRole = 'execution';
      const nlp = 'Find all active users';
      apiService.getAiResponse.withArgs(jasmine.any(Array), '', 'execution').and.returnValue(of({ choices: [{ message: { content: `[[QUERY_REQUIRED]] ${nlp}` } }] }).pipe(delay(1)));
      const stateMachineSpy = spyOn((service as any).stateMachine, 'next');

      (service as any).handleExecution();
      tick(1);

      expect(apiService.getAiResponse).toHaveBeenCalled();
      expect(stateMachineSpy).toHaveBeenCalledWith({ role: 'query_generation', data: nlp });
    }));

    it('should transition to the next step when AI returns [[STEP_COMPLETE]]', fakeAsync(() => {
      service.breakdownSteps = ['Step 1', 'Step 2'];
      (service as any).currentStepIndex = 0;
      service.currentAiRole = 'execution';
      apiService.getAiResponse.withArgs(jasmine.any(Array), '', 'execution').and.returnValue(of({ choices: [{ message: { content: '[[STEP_COMPLETE]]' } }] }).pipe(delay(1)));
      const stateMachineSpy = spyOn((service as any).stateMachine, 'next');

      (service as any).handleExecution();
      tick(1);

      expect((service as any).currentStepIndex).toBe(1);
      expect(stateMachineSpy).toHaveBeenCalledWith({ role: 'execution' });
    }));

    it('should transition to finalization when all steps are complete', fakeAsync(() => {
      service.breakdownSteps = ['Step 1'];
      (service as any).currentStepIndex = 1;
      service.currentAiRole = 'execution';
      const stateMachineSpy = spyOn((service as any).stateMachine, 'next');

      (service as any).handleExecution();
      tick(1);

      expect(stateMachineSpy).toHaveBeenCalledWith({ role: 'finalization' });
    }));

    it('should handle API error and stop the process', fakeAsync(() => {
        service.breakdownSteps = ['Step 1: Error'];
        (service as any).currentStepIndex = 0;
        service.currentAiRole = 'execution';
        const errorResponse = { status: 500, statusText: 'Internal Server Error' };
        apiService.getAiResponse.withArgs(jasmine.any(Array), '', 'execution').and.returnValue(throwError(() => errorResponse).pipe(delay(1)));
  
        (service as any).handleExecution();
        tick(1);
  
        expect(service.messages.length).toBe(1);
        expect(service.messages[0].content).toContain('Error: Could not get a response from the Execution AI');
      }));
  });
});