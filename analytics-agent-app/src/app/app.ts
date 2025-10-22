import { Component, ViewChild, ElementRef, AfterViewChecked } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms'; // Import FormsModule
import { ApiService } from './api'; // Corrected import path for ApiService

interface Message {
  sender: 'user' | 'ai';
  content: string;
}

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, FormsModule], // Add FormsModule here
  templateUrl: './app.component.html',
  styleUrl: './app.css'
})
export class AppComponent implements AfterViewChecked {
  title = 'analytics-agent';
  messages: Message[] = [];
  newMessage: string = '';
  isLoading: boolean = false; // Add isLoading property

  @ViewChild('chatContainer') private chatContainer!: ElementRef;
  @ViewChild('messageInput') private messageInput!: ElementRef;

  constructor(private apiService: ApiService) { // Inject ApiService
    // Initial AI message
    this.messages.push({
      sender: 'ai',
      content: 'Hello! How can I help you today?'
    });
  }

  ngAfterViewChecked(): void {
    this.scrollToBottom();
  }

  adjustTextareaHeight(element: HTMLTextAreaElement): void {
    element.style.height = 'auto';
    element.style.height = element.scrollHeight + 'px';
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
      },
      error: (error: any) => { // Explicitly type error
        console.error('Error fetching AI response:', error);
        this.messages.push({
          sender: 'ai',
          content: 'Error: Could not get a response from the AI.'
        });
        this.isLoading = false; // Set loading to false after error
      }
    });
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