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

describe('ChatOrchestratorService: Analysis AI', () => {
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

    // Common setup for execution phase tests
    service.newMessage = 'Test message';
    mockApiService.getAiResponse.withArgs(jasmine.any(Array), 'Test message', 'collaborate').and.returnValue(of({ choices: [{ message: { content: 'AI response [[COLLAB_DONE]]' } }] }).pipe(delay(1)));
  });

  describe('[2] Analysis AI', () => {
    it('should transition to breakdown phase on successful analysis', fakeAsync(() => {
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: 'Analysis AI output' } }] }).pipe(delay(1)));
      // Mock the next step to prevent further execution
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValue(of({ choices: [{ message: { content: '[]' } }] }).pipe(delay(1)));

      service.sendMessage();
      tick(1); // Resolve collaboration

      expect(service.currentAiRole).toBe('analyze');
      expect(service.thinkingMessage).toBe('Analyzing request...');

      tick(1); // Resolve analysis

      expect(service.execution_context).toEqual(['Analysis AI output']);
      expect(service.currentAiRole).toBe('breakdown');
      expect(service.thinkingMessage).toBe('Breaking down the task into steps...');
    }));

    it('should handle API error during analysis', fakeAsync(() => {
      const errorResponse = { status: 500, statusText: 'Internal Server Error' };
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(throwError(() => errorResponse).pipe(delay(1)));

      service.sendMessage();
      tick(1); // Resolve collaboration

      expect(service.currentAiRole).toBe('analyze');
      expect(service.thinkingMessage).toBe('Analyzing request...');

      tick(1); // Resolve analysis (error)

      expect(service.messages.slice(-1)[0].content).toBe('Error: Could not get a response from the Analysis AI.');
      expect(service.isLoading).toBe(false);
      expect(service.showThinkingModal).toBe(false);
    }));

    it('should handle empty or invalid response from Analysis AI', fakeAsync(() => {
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'analyze').and.returnValue(of({ choices: [{ message: { content: null } }] }).pipe(delay(1)));
      // Mock the next step to prevent further execution
      mockApiService.getAiResponse.withArgs(jasmine.any(Array), '', 'breakdown').and.returnValue(of({ choices: [{ message: { content: '[]' } }] }).pipe(delay(1)));

      service.sendMessage();
      tick(1); // Resolve collaboration
      tick(1); // Resolve analysis

      expect(service.execution_context).toEqual(['No response from Analysis AI.']);
      expect(service.currentAiRole).toBe('breakdown');
    }));
  });
});
