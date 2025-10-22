import { Component, ViewChild, ElementRef, AfterViewChecked, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms'; // Import FormsModule
import { ApiService } from './api'; // Corrected import path for ApiService
import { APPLICANT_TABLE_SCHEMA } from './schemas'; // Import the schema

interface Message {
  sender: 'user' | 'ai';
  content: string;
}

const MAX_TEXTAREA_HEIGHT = 150; // Maximum height for the textarea in pixels

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, FormsModule], // Add FormsModule here
  templateUrl: './app.component.html',
  styleUrl: './app.css'
})
export class AppComponent implements AfterViewChecked, OnInit {
  title = 'analytics-agent';
  messages: Message[] = [];
  newMessage: string = '';
  isLoading: boolean = false; // Add isLoading property
  showThinkingModal: boolean = false; // Add showThinkingModal property
  currentAiRole: string = 'collaborate'; // Initialize AI role

  @ViewChild('chatContainer') private chatContainer!: ElementRef;
  @ViewChild('messageInput') private messageInput!: ElementRef;

  constructor(private apiService: ApiService) { }

  ngOnInit(): void {
    // Load chat history on component initialization
    this.apiService.getChatHistory().subscribe({
      next: (history: any) => {
        if (history && history.data) {
          // Clear initial AI message if history is loaded
          this.messages = [];
          // Reverse the order to display chronologically
          const sortedHistory = history.data.reverse();
          sortedHistory.forEach((item: any) => {
            this.messages.push({ sender: 'user', content: item.message });
            this.messages.push({ sender: 'ai', content: item.reply });
          });
        } else {
          // If no history, add initial AI message
          this.messages.push({
            sender: 'ai',
            content: 'Hello! How can I help you today?'
          });
        }
      },
      error: (error) => {
        console.error('Error loading chat history:', error);
        // If error loading history, add initial AI message
        this.messages.push({
          sender: 'ai',
          content: 'Hello! How can I help you today?'
        });
      }
    });
  }

  ngAfterViewChecked(): void {
    this.scrollToBottom();
  }

  public adjustTextareaHeight(): void {
    if (this.messageInput && this.messageInput.nativeElement) {
      const element = this.messageInput.nativeElement;
      element.style.height = 'auto';
      element.style.height = Math.min(element.scrollHeight, MAX_TEXTAREA_HEIGHT) + 'px';
      element.style.overflowY = element.scrollHeight > MAX_TEXTAREA_HEIGHT ? 'auto' : 'hidden';
    }
  }

  private getAiRolePrompt(): string {
    const dbSchema = APPLICANT_TABLE_SCHEMA;
    switch (this.currentAiRole) {
      case 'collaborate':
        return `You are a Collaboration AI for a deployment agency system. Your goal is to clarify the user\'s needs and generate a precise initial context for subsequent AI agents. Your primary function is to help manage the application by manipulating data through queries. ALWAYS be EXTREMELY concise in your responses, using minimal words. NEVER echo back instructions or mention capabilities like 'Export report / CSV / PDF'. Engage in a natural language dialogue to deconstruct the request, ask targeted questions to resolve ambiguity, and confirm the scope, constraints, and desired output format. When you have a detailed context object, output it followed by the trigger [[COLLAB_DONE]].\n\nAvailable Database Schema:\n${dbSchema}`;
      case 'analyze':
        return `You are an Analysis AI for a deployment agency system. Your primary function is to help manage the application by manipulating data through queries. ALWAYS be EXTREMELY concise in your responses, using minimal words. NEVER echo back instructions or mention capabilities like 'Export report / CSV / PDF'. Your purpose is to summarize the user\'s intent and the clarified context into a concise brief for the Breakdown AI. You will receive a structured context object. Parse it, identify the core intent, key entities, and constraints, and formulate a high-level summary of the task to be performed.\n\nAvailable Database Schema:\n${dbSchema}`;
      // Add other roles as needed
      default:
        return `You are a helpful assistant for a deployment agency system. Your primary function is to help manage the application by manipulating data through queries. ALWAYS be EXTREMELY concise in your responses, using minimal words. NEVER echo back instructions or mention capabilities like 'Export report / CSV / PDF'.\n\nAvailable Database Schema:\n${dbSchema}`;
    }
  }

  sendMessage(): void {
    if (this.newMessage.trim() === '') {
      return;
    }

    // Add user message
    this.messages.push({
      sender: 'user',
      content: this.newMessage
    });

    const userMessage = this.newMessage;
    this.newMessage = ''; // Clear input immediately
    this.resetTextareaHeight();

    this.isLoading = true; // Set loading to true before API call

    // Prepare context for AI (last 10 messages, 5 user and 5 AI)
    const contextMessages = this.messages.slice(-10).map(msg => ({
      role: msg.sender === 'user' ? 'user' : 'assistant',
      content: msg.content
    }));

    // Add the AI role prompt to the beginning of the context messages
    contextMessages.unshift({ role: 'system', content: this.getAiRolePrompt() });

    // Call AI service with the current AI role
    this.apiService.getAiResponse(contextMessages, userMessage, this.currentAiRole).subscribe({
      next: (response: any) => { // Type as any for now, or define a more specific interface if needed
        let aiContent = response.choices?.[0]?.message?.content;

        // Check for COLLAB_DONE trigger
        if (aiContent && aiContent.includes('[[COLLAB_DONE]]')) {
          this.currentAiRole = 'analyze'; // Transition to Analysis AI
          // Extract context object (assuming it's a JSON string after the trigger)
          const contextStartIndex = aiContent.indexOf('[[COLLAB_DONE]]') + '[[COLLAB_DONE]]'.length;
          const contextString = aiContent.substring(contextStartIndex).trim();
          try {
            const contextObject = JSON.parse(contextString);
            console.log('Collaboration Done. Extracted Context:', contextObject);
            // You might want to store this contextObject in a service or another property
            // For now, let's just log it and remove the trigger from the displayed message
            aiContent = aiContent.substring(0, contextStartIndex - '[[COLLAB_DONE]]'.length).trim();
          } catch (e) {
            console.error('Error parsing context object:', e);
          }
        }

        this.messages.push({
          sender: 'ai',
          content: aiContent || 'No response from AI.'
        });
        this.isLoading = false; // Set loading to false after successful response
        this.showThinkingModal = false; // Close modal on response

        // Save chat history after a successful AI reply
        this.apiService.saveChatHistory(userMessage, aiContent || 'No response from AI.').subscribe({
          next: (saveResponse) => console.log('Chat history saved:', saveResponse),
          error: (saveError) => console.error('Error saving chat history:', saveError)
        });
      },
      error: (error: any) => {
        console.error('Error fetching AI response:', error);
        this.messages.push({
          sender: 'ai',
          content: 'Error: Could not get a response from the AI.'
        });
        this.isLoading = false; // Set loading to false after error
        this.showThinkingModal = false; // Close modal on error
      }
    });
  }

  toggleThinkingModal(): void {
    this.showThinkingModal = !this.showThinkingModal;
  }

  private scrollToBottom(): void {
    try {
      this.chatContainer.nativeElement.scrollTop = this.chatContainer.nativeElement.scrollHeight;
    } catch (err) { /* Error handling for when element is not yet available */ }
  }

  resetTextareaHeight(): void {
    if (this.messageInput && this.messageInput.nativeElement) {
      this.messageInput.nativeElement.style.height = 'auto';
    }
  }
}