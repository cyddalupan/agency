import { TestBed, fakeAsync, tick, waitForAsync } from '@angular/core/testing';
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

describe('ChatOrchestratorService: Query Generation AI', () => {
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

  describe('[5] Query Generation AI', () => {
    beforeEach(fakeAsync(() => {
      // Setup the service to be at the point where it's ready to process a query
      service.breakdownSteps = ['Step 1: Generate a query'];
      service.execution_context = ['Analysis output'];
      service.currentAiRole = 'execution';

      // Mock the execution step to trigger query generation
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'execution').and.returnValue(of({ choices: [{ message: { content: '[[QUERY_REQUIRED]] Get all users' } }] }).pipe(delay(1)));
    }));

    it('should retry query generation if the first attempt returns an unsafe query', fakeAsync(() => {
      // Arrange: Safety check fails first, then succeeds
      service.queryRetryCount = 0; // Initialize query retry count
      service.safetyRetryCount = 0; // Initialize safety retry count
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'query_generation').and.returnValues(
        of({ choices: [{ message: { content: 'DROP TABLE users;' } }] }).pipe(delay(1)),
        of({ choices: [{ message: { content: 'SELECT * FROM users;' } }] }).pipe(delay(1))
      );
      mockApiService.executeQuery.and.returnValue(of({ data: [] }).pipe(delay(1)));
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'finalization').and.returnValue(of({ choices: [{ message: { content: 'Finalized' } }] }).pipe(delay(1)));
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'html_conversion').and.returnValue(of({ choices: [{ message: { content: 'HTML' } }] }).pipe(delay(1)));

      // Act
      (service as any).stateMachine.next({ role: 'execution' });
      tick(1); // Execution AI -> [[QUERY_REQUIRED]]
      tick(1); // Query Generation AI -> 'DROP TABLE users;'

      // Assert: Retry logic is triggered
      expect(service.currentAiRole).toBe('query_generation');
      expect(service.safetyRetryCount).toBe(1);
      expect(service.execution_context).toContain('Safety Check: [[UNSAFE]]');
      expect(service.execution_context).toContain('SQL query failed safety check. Please generate a safe query.');

      // Act: Second attempt
      tick(1); // Query Generation AI -> 'SELECT * FROM users;'
      tick(1); // executeQuery -> success
      tick(1); // finalization
      tick(1); // html_conversion

      // Assert: Flow completes successfully
      expect(mockApiService.executeQuery).toHaveBeenCalledWith('SELECT * FROM users;', []);
      expect(service.isLoading).toBe(false);
    }));

    it('should retry query generation if the database execution fails', fakeAsync(() => {
      // Arrange: DB execution fails first, then succeeds
      service.queryRetryCount = 0; // Initialize query retry count
      service.safetyRetryCount = 0; // Initialize safety retry count
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'query_generation').and.returnValues(
        of({ choices: [{ message: { content: 'SELECT * FRM users;' } }] }).pipe(delay(1)), // Invalid SQL
        of({ choices: [{ message: { content: 'SELECT * FROM users;' } }] }).pipe(delay(1)) // Correct SQL
      );
      mockApiService.executeQuery.and.returnValues(
        throwError(() => ({ message: 'Syntax error' })).pipe(delay(1)), // Asynchronous error
        of({ data: [] }).pipe(delay(1))
      );
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'finalization').and.returnValue(of({ choices: [{ message: { content: 'Finalized' } }] }).pipe(delay(1)));
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'html_conversion').and.returnValue(of({ choices: [{ message: { content: 'HTML' } }] }).pipe(delay(1)));
    
      // Act
      (service as any).stateMachine.next({ role: 'execution' });
    
      tick(1); // Execution AI -> [[QUERY_REQUIRED]]
      tick(1); // Query Generation AI -> 'SELECT * FRM users;'

      // Assert: Retry logic is triggered
      expect(service.currentAiRole).toBe('query_generation');
      expect(service.queryRetryCount).toBe(1);
      expect(service.execution_context).toContain('Query execution failed: Syntax error. Please correct the SQL query.');
    
      // Act: Second attempt
      tick(1); // Query Generation AI -> 'SELECT * FROM users;'
      tick(1); // executeQuery -> success
    
      // Assert: Flow continues
      expect(mockApiService.executeQuery).toHaveBeenCalledWith('SELECT * FROM users;', []);
    
      tick(1); // finalization
      tick(1); // html_conversion
    
      // Assert: Flow completes successfully
      expect(service.isLoading).toBe(false);
    }));

    it('should fail after max retries for persistent database errors', waitForAsync(() => {
      // Arrange: DB execution fails repeatedly
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), jasmine.any(String), 'query_generation').and.returnValue(of({ choices: [{ message: { content: 'SELECT * FRM users;' } }] }).pipe(delay(1)));
      mockApiService.executeQuery.and.returnValue(throwError(() => ({ message: 'Syntax error' })).pipe(delay(1)));

      // Act
      (service as any).stateMachine.next({ role: 'execution' });

      // Assert
      setTimeout(() => {
        expect(service.isLoading).toBe(false);
        expect(service.messages.slice(-1)[0].content).toContain('Error: Failed to execute query after 5 attempts.');
        expect((service as any).queryRetryCount).toBe(0); // Should be reset to 0 after final failure
      }, 1000)
    }));
  });
});
