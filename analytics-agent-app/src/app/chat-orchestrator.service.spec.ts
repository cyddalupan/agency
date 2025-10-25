import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { of, throwError } from 'rxjs';

import { ChatOrchestratorService } from './chat-orchestrator.service';
import { ApiService } from './api';

class MockApiService {
  getChatHistory = jasmine.createSpy('getChatHistory').and.returnValue(of({ data: [] }));
  saveChatHistory = jasmine.createSpy('saveChatHistory').and.returnValue(of({}));
  getAiResponse = jasmine.createSpy('getAiResponse').and.returnValue(of({ choices: [{ message: { content: 'AI response' } }] }));
  executeQuery = jasmine.createSpy('executeQuery').and.returnValue(of({}));
  safetyRetryCount: number = 0;
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
});

describe('ChatOrchestratorService: isQuerySafe', () => {
  let service: ChatOrchestratorService;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [
        ChatOrchestratorService,
        { provide: ApiService, useClass: MockApiService }
      ]
    });
    service = TestBed.inject(ChatOrchestratorService);
  });

  // Safe queries
  it('should return true for a safe SELECT query', () => {
    const query = 'SELECT * FROM users WHERE name = "test"';
    expect(service.isQuerySafe(query)).toBe(true);
  });

  it('should return true for a safe INSERT query', () => {
    const query = 'INSERT INTO users (name) VALUES ("test")';
    expect(service.isQuerySafe(query)).toBe(true);
  });

  it('should return true for a safe UPDATE query with a WHERE clause', () => {
    const query = 'UPDATE users SET name = "new" WHERE id = 1';
    expect(service.isQuerySafe(query)).toBe(true);
  });

  it('should return true for a safe DELETE query with a WHERE clause', () => {
    const query = 'DELETE FROM users WHERE id = 1';
    expect(service.isQuerySafe(query)).toBe(true);
  });

  it('should return true for a query containing a forbidden keyword as a substring (e.g., dropdown)', () => {
    const query = "SELECT * FROM user_settings WHERE setting_name = 'dropdown_options'";
    expect(service.isQuerySafe(query)).toBe(true);
  });

  // Unsafe queries
  it('should return false for a DROP TABLE query', () => {
    const query = 'DROP TABLE users';
    expect(service.isQuerySafe(query)).toBe(false);
  });

  it('should return false for a TRUNCATE TABLE query', () => {
    const query = 'TRUNCATE TABLE users';
    expect(service.isQuerySafe(query)).toBe(false);
  });

  it('should return false for an ALTER TABLE query', () => {
    const query = 'ALTER TABLE users ADD COLUMN email VARCHAR(255)';
    expect(service.isQuerySafe(query)).toBe(false);
  });

  it('should return false for a GRANT query', () => {
    const query = 'GRANT SELECT ON users TO new_user';
    expect(service.isQuerySafe(query)).toBe(false);
  });

  it('should return false for a REVOKE query', () => {
    const query = 'REVOKE SELECT ON users FROM old_user';
    expect(service.isQuerySafe(query)).toBe(false);
  });

  it('should return false for a DELETE query without a WHERE clause', () => {
    const query = 'DELETE FROM users';
    expect(service.isQuerySafe(query)).toBe(false);
  });

  it('should return false for an UPDATE query without a WHERE clause', () => {
    const query = 'UPDATE users SET name = "hacked"';
    expect(service.isQuerySafe(query)).toBe(false);
  });

  it('should return false for a query that is just "where"', () => {
    const query = 'where';
    expect(service.isQuerySafe(query)).toBe(false);
  });
});

describe('ChatOrchestratorService: handleFinalization', () => {
  let service: ChatOrchestratorService;
  let mockApiService: MockApiService;
  let stateMachineSpy: jasmine.Spy;

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
    stateMachineSpy = spyOn(service['stateMachine'], 'next').and.callThrough();
  });

  it('should call apiService and transition to html_conversion on success', () => {
    // Arrange
    const finalizationResponse = { choices: [{ message: { content: 'Final summary' } }] };
    mockApiService.getAiResponse.and.returnValue(of(finalizationResponse));
    service.execution_context = ['step 1 result', 'step 2 result'];
    const htmlConversionSpy = spyOn<any>(service, 'handleHtmlConversion');

    // Act
    service['stateMachine'].next({ role: 'finalization' });

    // Assert
    expect(mockApiService.getAiResponse).toHaveBeenCalledWith(
      jasmine.any(Array),
      '',
      'finalization'
    );
    expect(htmlConversionSpy).toHaveBeenCalledWith('Final summary');
  });

  it('should handle API error and post a message', () => {
    // Arrange
    mockApiService.getAiResponse.and.returnValue(throwError(() => new Error('API Error')));
    service.execution_context = ['step 1 result'];
    service.messages = [];
    service.isLoading = true;
    const htmlConversionSpy = spyOn<any>(service, 'handleHtmlConversion');

    // Act
    service['stateMachine'].next({ role: 'finalization' });

    // Assert
    expect(service.messages.length).toBe(1);
    expect(service.messages[0].content).toBe('Error: Could not get a finalization message from the AI.');
    expect(service.isLoading).toBe(false);
    expect(htmlConversionSpy).not.toHaveBeenCalled();
  });

  it('should handle empty AI response and transition with a default message', () => {
    // Arrange
    const emptyResponse = { choices: [{ message: { content: null } }] };
    mockApiService.getAiResponse.and.returnValue(of(emptyResponse));
    service.execution_context = ['step 1 result'];
    const htmlConversionSpy = spyOn<any>(service, 'handleHtmlConversion');

    // Act
    service['stateMachine'].next({ role: 'finalization' });

    // Assert
    expect(mockApiService.getAiResponse).toHaveBeenCalled();
    expect(htmlConversionSpy).toHaveBeenCalledWith('No finalization message from AI.');
  });
});
