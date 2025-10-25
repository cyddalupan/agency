
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
  let handleFatalErrorSpy: jasmine.Spy;

  beforeAll(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [
        ChatOrchestratorService,
        { provide: ApiService, useClass: MockApiService }
      ]
    });
    service = TestBed.inject(ChatOrchestratorService);
    apiService = TestBed.inject(ApiService) as unknown as MockApiService;
    handleFatalErrorSpy = spyOn(service as any, 'handleFatalError').and.callThrough();
  });

  beforeEach(() => {
    // Reset service state for each test
    service.queryRetryCount = 0;
    service.safetyRetryCount = 0;
    service.breakdownRetryCount = 0;
    service.execution_context = [];
    service.messages = [];
    (apiService.getAiResponse as jasmine.Spy).calls.reset();
    (apiService.executeQuery as jasmine.Spy).calls.reset();
    (apiService.saveChatHistory as jasmine.Spy).calls.reset();
    handleFatalErrorSpy.calls.reset(); // Reset the spy calls here
  });

  it('should execute a query successfully on the first attempt', fakeAsync(() => {
    const query = 'SELECT * FROM users';
    const nlp = 'get all users';
    const queryResult = { data: [{ id: 1, name: 'John Doe' }] };
    apiService.executeQuery.and.returnValue(of(queryResult).pipe(delay(1)));
    spyOn(service as any, 'handleExecution').and.stub();

    (service as any).handleSafetyCheck({ query, nlp });
    tick(1);

    expect(apiService.executeQuery).toHaveBeenCalledWith(query, []);
    expect(service.queryRetryCount).toBe(0);
    expect((service as any).handleExecution).toHaveBeenCalled();
    expect(handleFatalErrorSpy).not.toHaveBeenCalled();
  }));

  it('should retry once and then succeed', fakeAsync(() => {
    const query = 'SELECT * FROM users';
    const nlp = 'get all users';
    const queryError = { message: 'Syntax error' };
    const queryResult = { data: [] };
    const correctedQuery = 'SELECT * FROM users WHERE 1=1';

    apiService.executeQuery.and.returnValues(
      throwError(() => queryError).pipe(delay(1)),
      of(queryResult).pipe(delay(1))
    );
    apiService.getAiResponse.and.returnValue(of({ choices: [{ message: { content: correctedQuery } }] }).pipe(delay(1)));
    spyOn(service as any, 'handleExecution').and.stub();

    (service as any).handleSafetyCheck({ query, nlp });
    tick(1); // Initial executeQuery call
    expect(service.queryRetryCount).toBe(1);
    expect(apiService.getAiResponse).toHaveBeenCalledWith(jasmine.any(Array), '', 'query_generation');

    tick(1); // handleQueryGeneration call
    expect(service.execution_context).toContain(`Generated SQL: ${correctedQuery}`);

    tick(1); // Retry executeQuery call

    expect(apiService.executeQuery).toHaveBeenCalledTimes(2);
    expect(service.queryRetryCount).toBe(0);
    expect((service as any).handleExecution).toHaveBeenCalled();
    expect(handleFatalErrorSpy).not.toHaveBeenCalled();
  }));

  it('should fail after 5 attempts and call fatal error handler', fakeAsync(() => {
    const query = 'SELECT * FROM users';
    const nlp = 'get all users';
    const queryError = { message: 'Syntax error' };

    apiService.executeQuery.and.returnValue(throwError(() => queryError).pipe(delay(1)));
    apiService.getAiResponse.and.returnValue(of({ choices: [{ message: { content: query } }] }).pipe(delay(1)));

    (service as any).handleSafetyCheck({ query, nlp });
    
    for (let i = 0; i < 5; i++) {
      tick(1); // executeQuery call
      if (i < 4) { // query_generation is called 4 times before the 5th failure
        tick(1); // getAiResponse call
      }
    }

    expect(apiService.executeQuery).toHaveBeenCalledTimes(5);
    expect(handleFatalErrorSpy).toHaveBeenCalledWith(`Error: Failed to execute query after 5 attempts. Please refine your request.`);
  }));

  it('should fail immediately on non-recoverable error and call fatal error handler', fakeAsync(() => {
    const query = 'SELECT * FROM users';
    const nlp = 'get all users';
    const serverError = { status: 500, message: 'Internal Server Error' };

    apiService.executeQuery.and.returnValue(throwError(() => serverError).pipe(delay(1)));

    (service as any).handleSafetyCheck({ query, nlp });
    tick(1);

    expect(apiService.executeQuery).toHaveBeenCalledTimes(1);
    expect(handleFatalErrorSpy).toHaveBeenCalledWith(`Error: Failed to execute query after 1 attempts. Please refine your request.`);
  }));
});
