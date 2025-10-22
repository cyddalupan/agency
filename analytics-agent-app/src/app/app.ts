import { Component, ViewChild, ElementRef, AfterViewChecked, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms'; // Import FormsModule
import { ApiService } from './api'; // Corrected import path for ApiService

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
          history.data.forEach((item: any) => {
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

  adjustTextareaHeight(element: HTMLTextAreaElement): void {
    element.style.height = 'auto';
    element.style.height = Math.min(element.scrollHeight, MAX_TEXTAREA_HEIGHT) + 'px';
    element.style.overflowY = element.scrollHeight > MAX_TEXTAREA_HEIGHT ? 'auto' : 'hidden';
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

    // Call AI service
    this.apiService.getAiResponse([], userMessage).subscribe({
      next: (response: any) => { // Type as any for now, or define a more specific interface if needed
        const aiContent = response.choices?.[0]?.message?.content;
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

  private resetTextareaHeight(): void {
    if (this.messageInput && this.messageInput.nativeElement) {
      this.messageInput.nativeElement.style.height = 'auto';
    }
  }
}