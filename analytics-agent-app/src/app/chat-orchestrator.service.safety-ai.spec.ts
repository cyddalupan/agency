import { TestBed, fakeAsync, tick } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { of } from 'rxjs';
import { delay } from 'rxjs/operators';

import { ChatOrchestratorService } from './chat-orchestrator.service';
import { ApiService } from './api';

class MockApiService {
  getAiResponse = jasmine.createSpy('getAiResponse').and.returnValue(of({ choices: [{ message: { content: 'AI response' } }] }));
  executeQuery = jasmine.createSpy('executeQuery').and.returnValue(of({}));
  saveChatHistory = jasmine.createSpy('saveChatHistory').and.returnValue(of({}));
}

describe('ChatOrchestratorService: Safety AI', () => {
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

  describe('[6] Safety AI (AI Check)', () => {
    const safeQuery = 'SELECT * FROM users;';
    const unsafeQuery = 'DROP TABLE users;';
    const nlp = 'some natural language query';

    it('should proceed with execution when AI returns [[SAFE_TO_RUN]]', fakeAsync(() => {
      service.currentAiRole = 'safety_check';
      apiService.getAiResponse.withArgs(jasmine.any(Array), safeQuery, 'safety_check').and.returnValue(of({ choices: [{ message: { content: '[[SAFE_TO_RUN]]' } }] }).pipe(delay(1)));
      const executeSpy = spyOn(service as any, 'executeQueryWithRetries').and.stub();

      (service as any).handleSafetyCheck({ query: safeQuery, nlp });
      tick(1);

      expect(executeSpy).toHaveBeenCalledWith({ query: safeQuery, nlp });
    }));

    it('should trigger query_generation retry when AI returns [[UNSAFE:]]', fakeAsync(() => {
      service.currentAiRole = 'safety_check';
      service.safetyRetryCount = 0;
      apiService.getAiResponse.withArgs(jasmine.any(Array), unsafeQuery, 'safety_check').and.returnValue(of({ choices: [{ message: { content: '[[UNSAFE: Contains DROP statement]]' } }] }).pipe(delay(1)));
      const stateMachineSpy = spyOn((service as any).stateMachine, 'next');

      (service as any).handleSafetyCheck({ query: unsafeQuery, nlp });
      tick(1);

      expect(service.safetyRetryCount).toBe(1);
      expect(stateMachineSpy).toHaveBeenCalledWith({ role: 'query_generation', data: nlp });
    }));

    it('should call handleFatalError after max retries', fakeAsync(() => {
      service.currentAiRole = 'safety_check';
      service.safetyRetryCount = (service as any).MAX_QUERY_RETRIES;
      apiService.getAiResponse.withArgs(jasmine.any(Array), unsafeQuery, 'safety_check').and.returnValue(of({ choices: [{ message: { content: '[[UNSAFE: Contains DROP statement]]' } }] }).pipe(delay(1)));
      const fatalErrorSpy = spyOn(service as any, 'handleFatalError').and.stub();

      (service as any).handleSafetyCheck({ query: unsafeQuery, nlp });
      tick(1);

      expect(fatalErrorSpy).toHaveBeenCalled();
    }));
  });
});