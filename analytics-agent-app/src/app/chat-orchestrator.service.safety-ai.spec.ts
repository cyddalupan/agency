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

describe('ChatOrchestratorService: Safety AI', () => {
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

  describe('[6] Safety AI (Local Check)', () => {
    it('should approve a safe SELECT query', () => {
      const query = "SELECT id, name FROM users WHERE status = 'active';";
      expect((service as any).isQuerySafe(query)).toBe(true);
    });

    it('should approve a safe SELECT query with a JOIN', () => {
      const query = 'SELECT u.id, p.name FROM users u JOIN profiles p ON u.id = p.user_id;';
      expect((service as any).isQuerySafe(query)).toBe(true);
    });

    it('should approve a safe UPDATE query with a WHERE clause', () => {
      const query = "UPDATE users SET name = 'John' WHERE id = 1;";
      expect((service as any).isQuerySafe(query)).toBe(true);
    });

    it('should approve a safe DELETE query with a WHERE clause', () => {
      const query = 'DELETE FROM users WHERE id = 1;';
      expect((service as any).isQuerySafe(query)).toBe(true);
    });

    it('should reject a query containing DROP', () => {
      const query = 'DROP TABLE users;';
      expect((service as any).isQuerySafe(query)).toBe(false);
    });

    it('should reject a query containing TRUNCATE', () => {
      const query = 'TRUNCATE TABLE users;';
      expect((service as any).isQuerySafe(query)).toBe(false);
    });

    it('should reject a query containing ALTER', () => {
      const query = 'ALTER TABLE users ADD COLUMN email VARCHAR(255);';
      expect((service as any).isQuerySafe(query)).toBe(false);
    });

    it('should reject a query containing GRANT', () => {
      const query = 'GRANT SELECT ON users TO public;';
      expect((service as any).isQuerySafe(query)).toBe(false);
    });

    it('should reject a query containing REVOKE', () => {
      const query = 'REVOKE SELECT ON users FROM public;';
      expect((service as any).isQuerySafe(query)).toBe(false);
    });

    it('should reject an UPDATE query without a WHERE clause', () => {
      const query = "UPDATE users SET status = 'inactive';";
      expect((service as any).isQuerySafe(query)).toBe(false);
    });

    it('should reject a DELETE query without a WHERE clause', () => {
      const query = 'DELETE FROM users;';
      expect((service as any).isQuerySafe(query)).toBe(false);
    });

    it('should reject a query that is just the keyword WHERE', () => {
      const query = 'WHERE';
      expect((service as any).isQuerySafe(query)).toBe(false);
    });

    it('should handle complex queries and still find unsafe keywords', () => {
      const query = 'SELECT * FROM users; TRUNCATE TABLE logs;';
      expect((service as any).isQuerySafe(query)).toBe(false);
    });

    it('should handle different casing for unsafe keywords', () => {
      const query = 'delete from users';
      expect((service as any).isQuerySafe(query)).toBe(false);
    });
  });
});
