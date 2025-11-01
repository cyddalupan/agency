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

describe('ChatOrchestratorService: handleHtmlConversion', () => {
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

  it('should call apiService and push HTML content to messages on success', () => {
    // Arrange
    const contentToConvert = 'Final summary text';
    const htmlResponse = { choices: [{ message: { content: '<p>Final summary HTML</p>' } }] };
    mockApiService.getAiResponse.and.returnValue(of(htmlResponse));
    service.messages = [];
    service.isLoading = true;

    // Act
    service['stateMachine'].next({ role: 'html_conversion', data: contentToConvert });

    // Assert
    expect(service.currentAiRole).toBe('html_conversion');
    expect(mockApiService.getAiResponse).toHaveBeenCalledWith(
      jasmine.any(Array),
      '',
      'html_conversion'
    );
    expect(service.messages.length).toBe(1);
    expect(service.messages[0].content).toBe('<p>Final summary HTML</p>');
    expect(service.isLoading).toBe(false);
    expect(mockApiService.saveChatHistory).toHaveBeenCalled();
  });

  it('should handle API error and post an error message', () => {
    // Arrange
    const contentToConvert = 'Some content';
    mockApiService.getAiResponse.and.returnValue(throwError(() => new Error('API Error')));
    service.messages = [];
    service.isLoading = true;

    // Act
    service['stateMachine'].next({ role: 'html_conversion', data: contentToConvert });

    // Assert
    expect(service.messages.length).toBe(1);
    expect(service.messages[0].content).toBe('Error: Could not get a response from the HTML Conversion AI.');
    expect(service.isLoading).toBe(false);
  });

  it('should handle empty AI response and post a default message', () => {
    // Arrange
    const contentToConvert = 'Some content';
    const emptyResponse = { choices: [{ message: { content: null } }] };
    mockApiService.getAiResponse.and.returnValue(of(emptyResponse));
    service.messages = [];
    service.isLoading = true;

    // Act
    service['stateMachine'].next({ role: 'html_conversion', data: contentToConvert });

    // Assert
    expect(service.messages.length).toBe(1);
    expect(service.messages[0].content).toBe('No HTML response from AI.');
    expect(service.isLoading).toBe(false);
  });

  it('should post an error if input content is null', () => {
    // Arrange
    const contentToConvert = null;
    service.messages = [];
    service.isLoading = true;

    // Act
    service['stateMachine'].next({ role: 'html_conversion', data: contentToConvert });

    // Assert
    expect(mockApiService.getAiResponse).not.toHaveBeenCalled();
    expect(service.isLoading).toBe(false);
    expect(service.messages.length).toBe(1);
    expect(service.messages[0].content).toBe('Error: Input to HTML conversion was null or undefined.');
  });
});

describe('ChatOrchestratorService: AI Interaction Limiter', () => {
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

  it('should handle a fatal error and post a message if AI interactions exceed the limit', () => {
    // Arrange: Set the counter to be at the limit
    const limit = 15;
    (service as any).aiInteractionCount = limit;
    (service as any).MAX_AI_INTERACTIONS = limit;

    const fatalErrorSpy = spyOn<any>(service, 'handleFatalError').and.callThrough();

    // Act: Trigger the next AI interaction directly via the handler
    service['handleCollaborate']('This message should trigger the error');

    // Assert: Check that the fatal error handler was called with the correct message
    expect(fatalErrorSpy).toHaveBeenCalledWith('Error: Maximum AI interactions exceeded for this request.');
    
    // Also assert the user-facing outcome
    const lastMessage = service.messages[service.messages.length - 1];
    expect(lastMessage.sender).toBe('ai');
    expect(lastMessage.content).toContain('Error: Maximum AI interactions exceeded for this request.');
    expect(service.isLoading).toBe(false);
  });
});

describe('ChatOrchestratorService: loadChatHistory', () => {
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

  it('should load history and populate messages array in correct order', () => {
    // Arrange
    const historyResponse = {
      data: [
        { message: 'Hello', reply: 'Hi there!' },
        { message: 'How are you?', reply: 'I am fine.' }
      ]
    };
    mockApiService.executeQuery.and.returnValue(of(historyResponse));
    service.messages = []; // Clear initial messages

    // Act
    service.loadChatHistory();

    // Assert
    expect(mockApiService.executeQuery).toHaveBeenCalledWith('SELECT message, reply FROM chat_history ORDER BY timestamp ASC LIMIT 20', []);
    expect(service.messages.length).toBe(4);
    expect(service.messages[0]).toEqual({ sender: 'user', content: 'Hello' });
    expect(service.messages[1]).toEqual({ sender: 'ai', content: 'Hi there!' });
    expect(service.messages[2]).toEqual({ sender: 'user', content: 'How are you?' });
    expect(service.messages[3]).toEqual({ sender: 'ai', content: 'I am fine.' });
  });

  it('should handle empty history response', () => {
    // Arrange
    const historyResponse = { data: [] };
    mockApiService.executeQuery.and.returnValue(of(historyResponse));
    service.messages = [];

    // Act
    service.loadChatHistory();

    // Assert
    expect(service.messages.length).toBe(0);
  });

  it('should handle API error gracefully', () => {
    // Arrange
    mockApiService.executeQuery.and.returnValue(throwError(() => new Error('API Error')));
    service.messages = [];
    const consoleErrorSpy = spyOn(console, 'error');

    // Act
    service.loadChatHistory();

    // Assert
    expect(service.messages.length).toBe(0);
    expect(consoleErrorSpy).toHaveBeenCalledWith('Error loading chat history:', jasmine.any(Error));
  });
});

describe('ChatOrchestratorService: handleSafetyCheck', () => {
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
    // Spy on executeQueryWithRetries to prevent the chain from continuing
    spyOn<any>(service, 'executeQueryWithRetries').and.callFake(() => {});
  });

  it('should pass execution_context to the Safety AI', () => {
    // Arrange
    const testExecutionContext = ['analysis result', 'some previous step'];
    const testQuery = 'SELECT * FROM users';
    service.execution_context = testExecutionContext;
    service.currentAiRole = 'safety_check'; // Set the role directly for this test
    mockApiService.getAiResponse.and.returnValue(of({ choices: [{ message: { content: '[[SAFE_TO_RUN]]' } }] }));

    // Act
    (service as any).handleSafetyCheck({ query: testQuery, nlp: 'get users' });

    // Assert
    expect(mockApiService.getAiResponse).toHaveBeenCalledWith(
      [
        jasmine.objectContaining({ role: 'system' }),
        jasmine.objectContaining({ role: 'assistant', content: 'analysis result' }),
        jasmine.objectContaining({ role: 'assistant', content: 'some previous step' })
      ],
      testQuery,
      'safety_check'
    );
  });
});