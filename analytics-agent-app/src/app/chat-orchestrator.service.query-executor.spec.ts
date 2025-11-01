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

describe('ChatOrchestratorService: Query Executor', () => {
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

  describe('[7] Query Executor', () => {
    const query = 'SELECT * FROM users';
    const nlp = 'get all users';

    it('should execute a query and transition to execution', fakeAsync(() => {
      const queryResult = { data: [] };
      apiService.executeQuery.and.returnValue(of(queryResult).pipe(delay(1)));
      const stateMachineSpy = spyOn((service as any).stateMachine, 'next');

      (service as any).executeQueryWithRetries({ query, nlp });
      tick(1);

      expect(apiService.executeQuery).toHaveBeenCalledWith(query, []);
      expect(service.execution_context).toContain(`Query Result: ${JSON.stringify(queryResult)}`);
      expect(stateMachineSpy).toHaveBeenCalledWith({ role: 'execution' });
    }));

    it('should retry to query_generation on a recoverable error', fakeAsync(() => {
      // Simulate the nested error structure from HttpErrorResponse
      const queryError = {
        error: { error: 'Syntax error' }
      };
      service.queryRetryCount = 0;
      // Clear context for this test
      service.execution_context = [];
      apiService.executeQuery.and.returnValue(throwError(() => queryError).pipe(delay(1)));
      const stateMachineSpy = spyOn((service as any).stateMachine, 'next');

      (service as any).executeQueryWithRetries({ query, nlp });
      tick(1);

      expect(service.queryRetryCount).toBe(1);
      // Check if the specific error string was pushed to the context array
      expect(service.execution_context).toContain('SQL Error: Syntax error. Please correct the SQL query.');
      expect(stateMachineSpy).toHaveBeenCalledWith({ role: 'query_generation', data: nlp });
    }));

    it('should retry on a 500 error and extract the error message from the JSON body', fakeAsync(() => {
      const detailedError = 'Unique constraint violation';
      const serverError = {
        status: 500,
        error: { error: detailedError }
      };
      service.queryRetryCount = 0;
      service.execution_context = [];
      apiService.executeQuery.and.returnValue(throwError(() => serverError).pipe(delay(1)));
      const stateMachineSpy = spyOn((service as any).stateMachine, 'next');

      (service as any).executeQueryWithRetries({ query, nlp });
      tick(1);

      expect(service.queryRetryCount).toBe(1);
      expect(service.execution_context).toContain(`SQL Error: ${detailedError}. Please correct the SQL query.`);
      expect(stateMachineSpy).toHaveBeenCalledWith({ role: 'query_generation', data: nlp });
    }));

    it('should retry on an error and extract the error message from a raw string body', fakeAsync(() => {
      const rawErrorString = 'Fatal PHP error';
      const serverError = {
        status: 500,
        error: rawErrorString
      };
      service.queryRetryCount = 0;
      service.execution_context = [];
      apiService.executeQuery.and.returnValue(throwError(() => serverError).pipe(delay(1)));
      const stateMachineSpy = spyOn((service as any).stateMachine, 'next');

      (service as any).executeQueryWithRetries({ query, nlp });
      tick(1);

      expect(service.queryRetryCount).toBe(1);
      expect(service.execution_context).toContain(`SQL Error: ${rawErrorString}. Please correct the SQL query.`);
      expect(stateMachineSpy).toHaveBeenCalledWith({ role: 'query_generation', data: nlp });
    }));

    it('should call handleFatalError after max retries', fakeAsync(() => {
      const queryError = { message: 'Syntax error' };
      service.queryRetryCount = (service as any).MAX_QUERY_RETRIES;
      apiService.executeQuery.and.returnValue(throwError(() => queryError).pipe(delay(1)));
      const fatalErrorSpy = spyOn(service as any, 'handleFatalError').and.stub();

      (service as any).executeQueryWithRetries({ query, nlp });
      tick(1);

      expect(fatalErrorSpy).toHaveBeenCalled();
    }));
  });
});