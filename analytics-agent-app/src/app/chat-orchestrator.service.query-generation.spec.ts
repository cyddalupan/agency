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

describe('ChatOrchestratorService: Query Generation AI', () => {
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

  describe('[5] Query Generation AI', () => {
    it('should generate a query and transition to safety_check', fakeAsync(() => {
      const nlp = 'get all users';
      const sqlQuery = 'SELECT * FROM users';
      service.currentAiRole = 'query_generation';
      apiService.getAiResponse.withArgs(jasmine.any(Array), '', 'query_generation').and.returnValue(of({ choices: [{ message: { content: sqlQuery } }] }).pipe(delay(1)));
      const stateMachineSpy = spyOn((service as any).stateMachine, 'next');

      (service as any).handleQueryGeneration(nlp);
      tick(1);

      expect(apiService.getAiResponse).toHaveBeenCalled();
      expect(service.execution_context).toContain(`Generated SQL: ${sqlQuery}`);
      expect(stateMachineSpy).toHaveBeenCalledWith({ role: 'safety_check', data: { query: sqlQuery, nlp: nlp } });
    }));

    it('should retry if AI fails to generate a query', fakeAsync(() => {
      const nlp = 'get all users';
      service.currentAiRole = 'query_generation';
      service.queryRetryCount = 0;
      apiService.getAiResponse.withArgs(jasmine.any(Array), '', 'query_generation').and.returnValue(of({ choices: [{ message: { content: null } }] }).pipe(delay(1)));
      const stateMachineSpy = spyOn((service as any).stateMachine, 'next');

      (service as any).handleQueryGeneration(nlp);
      tick(1);

      expect(service.queryRetryCount).toBe(1);
      expect(stateMachineSpy).toHaveBeenCalledWith({ role: 'query_generation', data: nlp });
    }));

    it('should fail after max retries for query generation', fakeAsync(() => {
      const nlp = 'get all users';
      service.currentAiRole = 'query_generation';
      service.queryRetryCount = (service as any).MAX_QUERY_RETRIES;
      apiService.getAiResponse.withArgs(jasmine.any(Array), '', 'query_generation').and.returnValue(of({ choices: [{ message: { content: null } }] }).pipe(delay(1)));

      (service as any).handleQueryGeneration(nlp);
      tick(1);

      expect(service.messages.length).toBe(1);
      expect(service.messages[0].content).toContain('Error: Failed to generate a SQL query');
    }));
  });
});
