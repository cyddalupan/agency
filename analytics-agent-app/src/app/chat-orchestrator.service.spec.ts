import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { of } from 'rxjs';

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
