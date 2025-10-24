import { TestBed, ComponentFixture } from '@angular/core/testing';
import { ElementRef } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { AppComponent } from './app';
import { ChatOrchestratorService } from './chat-orchestrator.service';

class MockChatOrchestratorService {
  messages: any[] = [];
  newMessage = '';
  isLoading = false;
  showThinkingModal = false;
  thinkingMessage = '';
  breakdownSteps: string[] = [];
  execution_context: string[] = [];
  sendMessage = jasmine.createSpy('sendMessage');
}

describe('AppComponent', () => {
  let component: AppComponent;
  let fixture: ComponentFixture<AppComponent>;
  let mockOrchestrator: MockChatOrchestratorService;
  let mockMessageInput: any;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AppComponent, FormsModule, CommonModule],
      providers: [
        { provide: ChatOrchestratorService, useClass: MockChatOrchestratorService }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(AppComponent);
    component = fixture.componentInstance;
    mockOrchestrator = TestBed.inject(ChatOrchestratorService) as unknown as MockChatOrchestratorService;

    mockMessageInput = {
      style: { height: 'auto', overflowY: 'hidden' },
      get scrollHeight() { return this._scrollHeight; },
      set scrollHeight(value) { this._scrollHeight = value; },
      _scrollHeight: 50,
    };
    (component as any).messageInput = new ElementRef(mockMessageInput);
  });

  it('should create the app', () => {
    expect(component).toBeTruthy();
  });

  describe('ngOnInit', () => {
    it('should add initial AI message on init', () => {
      component.ngOnInit();
      expect(mockOrchestrator.messages.length).toBe(1);
      expect(mockOrchestrator.messages[0].content).toBe('Hello! How can I help you today?');
    });
  });

  describe('sendMessage', () => {
    it('should call the orchestrator\'s sendMessage method', () => {
      component.sendMessage();
      expect(mockOrchestrator.sendMessage).toHaveBeenCalled();
    });
  });

  describe('adjustTextareaHeight', () => {
    it('should adjust textarea height based on content', () => {
      mockMessageInput.scrollHeight = 50;
      component.adjustTextareaHeight();
      expect(mockMessageInput.style.height).toBe('50px');

      mockMessageInput.scrollHeight = 200;
      component.adjustTextareaHeight();
      expect(mockMessageInput.style.height).toBe('150px');
      expect(mockMessageInput.style.overflowY).toBe('auto');
    });
  });

  describe('toggleThinkingModal', () => {
    it('should toggle the showThinkingModal property on the orchestrator', () => {
      mockOrchestrator.showThinkingModal = false;
      component.toggleThinkingModal();
      expect(mockOrchestrator.showThinkingModal).toBe(true);
      component.toggleThinkingModal();
      expect(mockOrchestrator.showThinkingModal).toBe(false);
    });
  });
});
