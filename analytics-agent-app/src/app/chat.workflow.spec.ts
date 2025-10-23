
import { of, throwError, Observable } from 'rxjs';
import { ChatWorkflowOrchestrator } from './chat.workflow';

// --- Mock Services ---

interface ChatHistoryItem {
  author: 'user' | 'ai';
  content: string;
}

class MockHistoryService {
  history: ChatHistoryItem[] = [];
  save(item: ChatHistoryItem) {
    this.history.push(item);
  }
  getHistory() {
    return this.history;
  }
}

// A more realistic ApiService mock that returns RxJS Observables
class MockApiService {
  getAiResponse(context: any[], message: string, role: string): Observable<{ content: string }> {
    // This will be spied on and configured in each test
    return of({ content: 'default AI response' });
  }
  executeQuery(query: string): Observable<{ data?: any[]; error?: string }> {
    // This will be spied on and configured in each test
    return of({ data: [] });
  }
}

describe('ChatWorkflowOrchestrator', () => {

  let historyService: MockHistoryService;
  let apiService: MockApiService;
  let orchestrator: ChatWorkflowOrchestrator;

  beforeEach(() => {
    historyService = new MockHistoryService();
    apiService = new MockApiService();
    orchestrator = new ChatWorkflowOrchestrator(historyService, apiService);
  });

  it('should run the full successful workflow, including a query retry', async () => {
    // Arrange
    const getAiResponseSpy = spyOn(apiService, 'getAiResponse').and.callFake((ctx, msg, role) => {
      if (role === 'collaborate') return of({ content: 'Okay, I will find those users for you. [[COLLAB_DONE]]' });
      if (role === 'analyze') return of({ content: 'User wants a list of inactive enterprise users.' });
      if (role === 'breakdown') return of({ content: '["Get inactive enterprise users query"]' });
      if (role === 'query_generation') {
        // Fail the first time, succeed the second time
        if (getAiResponseSpy.calls.count() <= 4) { // 1 collab, 1 analysis, 1 breakdown, 1st query gen
          return of({ content: 'SELECT * FROM users; -- FAIL' });
        } else {
          return of({ content: 'SELECT * FROM users WHERE active = false;' });
        }
      }
      if (role === 'finalization') return of({ content: 'Found 1 user: John Doe.' });
      if (role === 'html_conversion') return of({ content: '<div>Found 1 user: John Doe.</div>' });
      return of({ content: '' });
    });

    const executeQuerySpy = spyOn(apiService, 'executeQuery').and.callFake((query: string) => {
      if (query.includes('FAIL')) return of({ error: 'Syntax Error' });
      return of({ data: ['John Doe'] });
    });

    // Act
    await orchestrator.run('Find inactive enterprise users.');

    // Assert
    expect(getAiResponseSpy).toHaveBeenCalledWith(jasmine.any(Array), jasmine.any(String), 'collaborate');
    expect(getAiResponseSpy).toHaveBeenCalledWith(jasmine.any(Array), jasmine.any(String), 'analyze');
    expect(getAiResponseSpy).toHaveBeenCalledWith(jasmine.any(Array), jasmine.any(String), 'breakdown');
    expect(getAiResponseSpy).toHaveBeenCalledWith(jasmine.any(Array), jasmine.any(String), 'query_generation');
    expect(getAiResponseSpy).toHaveBeenCalledWith(jasmine.any(Array), jasmine.any(String), 'finalization');
    expect(getAiResponseSpy).toHaveBeenCalledWith(jasmine.any(Array), jasmine.any(String), 'html_conversion');
    expect(executeQuerySpy).toHaveBeenCalledTimes(2);
    expect(historyService.getHistory().pop()?.content).toContain('John Doe');
  });

  it('should halt and produce a final error message if query fails after max retries', async () => {
    // Arrange
    spyOn(apiService, 'getAiResponse').and.callFake((ctx, msg, role) => {
      if (role === 'collaborate') return of({ content: '[[COLLAB_DONE]]' });
      if (role === 'analyze') return of({ content: 'Test' });
      if (role === 'breakdown') return of({ content: '["Query step"]' });
      if (role === 'query_generation') return of({ content: 'SELECT *; -- FAIL' }); // Always generate failing query
      if (role === 'finalization') return of({ content: 'Query failed' });
      if (role === 'html_conversion') return of({ content: '<div>Query failed</div>' });
      return of({ content: '' });
    });
    const executeQuerySpy = spyOn(apiService, 'executeQuery').and.returnValue(of({ error: 'Syntax Error' }));

    // Act
    await orchestrator.run('test');

    // Assert
    expect(executeQuerySpy).toHaveBeenCalledTimes(5);
    expect(historyService.getHistory().pop()?.content).toContain('Query failed');
  });

  it('should retry query generation if an unsafe query is detected', async () => {
    // Arrange
    const getAiResponseSpy = spyOn(apiService, 'getAiResponse').and.callFake((ctx, msg, role) => {
      if (role === 'collaborate') return of({ content: '[[COLLAB_DONE]]' });
      if (role === 'analyze') return of({ content: 'Test' });
      if (role === 'breakdown') return of({ content: '["Query step"]' });
      if (role === 'query_generation') {
        // Return unsafe query first, then a safe one
        if (getAiResponseSpy.calls.count() <= 4) {
          return of({ content: 'DROP TABLE users;' });
        } else {
          return of({ content: 'SELECT * FROM users;' });
        }
      }
      if (role === 'finalization') return of({ content: 'Final result' });
      if (role === 'html_conversion') return of({ content: '<div>Final result</div>' });
      return of({ content: '' });
    });
    const executeQuerySpy = spyOn(apiService, 'executeQuery').and.returnValue(of({ data: [] }));

    // Act
    await orchestrator.run('test');

    // Assert
    expect(getAiResponseSpy.calls.allArgs().filter(args => args[2] === 'query_generation').length).toBe(2);
    expect(executeQuerySpy).toHaveBeenCalledTimes(1);
    expect(executeQuerySpy).toHaveBeenCalledWith('SELECT * FROM users;');
  });

  it('should handle failure in the Collaboration AI step', async () => {
    // Arrange
    spyOn(apiService, 'getAiResponse').and.callFake((ctx, msg, role) => {
      if (role === 'collaborate') {
        return throwError(() => new Error('AI service unavailable'));
      }
      if (role === 'finalization') {
        // Simulate the finalization AI reporting the error it found in the context
        const error = ctx.find(c => c.majorError);
        return of({ content: `Error: ${error.majorError}` });
      }
       if (role === 'html_conversion') {
        return of({ content: `<div>Error: ${msg}</div>`});
      }
      return of({ content: '' });
    });

    // Act
    await orchestrator.run('test');

    // Assert
    const finalHistory = historyService.getHistory();
    expect(finalHistory.pop()?.content).toContain('AI service unavailable');
  });
});
