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
  loadChatHistory = jasmine.createSpy('loadChatHistory');
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
    it('should call loadChatHistory on init', () => {
      component.ngOnInit();
      expect(mockOrchestrator.loadChatHistory).toHaveBeenCalled();
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

  describe('Thinking Modal', () => {
    it('should display two log containers when the modal is open', () => {
      mockOrchestrator.showThinkingModal = true;
      fixture.detectChanges();

      const planContainer = fixture.nativeElement.querySelector('.log-container-plan');
      const executionContainer = fixture.nativeElement.querySelector('.log-container-execution');

      expect(planContainer).toBeTruthy();
      expect(executionContainer).toBeTruthy();
    });

    it('should not display log containers when the modal is closed', () => {
      mockOrchestrator.showThinkingModal = false;
      fixture.detectChanges();

      const planContainer = fixture.nativeElement.querySelector('.log-container-plan');
      const executionContainer = fixture.nativeElement.querySelector('.log-container-execution');

      expect(planContainer).toBeFalsy();
      expect(executionContainer).toBeFalsy();
    });
  });

  describe('Breakdown AI Plan Display', () => {
    beforeEach(() => {
      mockOrchestrator.showThinkingModal = true;
    });

    it('should display the breakdown steps when the modal is open', () => {
      mockOrchestrator.breakdownSteps = ['Step 1: Analyze', 'Step 2: Query', 'Step 3: Format'];
      fixture.detectChanges();

      const stepElements = fixture.nativeElement.querySelectorAll('.log-container-plan li');
      expect(stepElements.length).toBe(3);
      expect(stepElements[0].textContent).toBe('Step 1: Analyze');
      expect(stepElements[1].textContent).toBe('Step 2: Query');
      expect(stepElements[2].textContent).toBe('Step 3: Format');
    });

    it('should display the "no steps" message when the breakdown steps array is empty', () => {
      mockOrchestrator.breakdownSteps = [];
      fixture.detectChanges();

      const noStepsMessage = fixture.nativeElement.querySelector('.log-container-plan p');
      expect(noStepsMessage).toBeTruthy();
      expect(noStepsMessage.textContent).toContain('No breakdown steps generated yet.');

      const stepElements = fixture.nativeElement.querySelectorAll('.log-container-plan li');
      expect(stepElements.length).toBe(0);
    });
  });
});
