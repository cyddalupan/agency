import { Component, ViewChild, ElementRef, AfterViewChecked, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms'; // Import FormsModule
import { ApiService } from './api'; // Corrected import path for ApiService

interface Message {
  sender: 'user' | 'ai';
  content: string;
}

const MAX_TEXTAREA_HEIGHT = 150; // Maximum height for the textarea in pixels

const APPLICANT_TABLE_SCHEMA = `
Table: applicant
Columns:
applicant_id (Full texts)
fra_ftw (Full texts)
agent_ppt (Full texts)
fra_visa (Full texts)
fra_deployed (Full texts)
fra_before (Full texts)
fra_sent (Full texts)
agent_ftw (Full texts)
agent_contract (Full texts)
agent_deployed (Full texts)
fra_remarks (Full texts)
applicantNumber (Full texts)
sub_employer (Full texts)
applicant_first (Full texts)
applicant_middle (Full texts)
applicant_last (Full texts)
password (Full texts)
applicant_suffix (Full texts)
applicant_birthdate (Full texts)
applicant_age (Full texts)
applicant_gender (Auto-compute)
applicant_contacts (Full texts)
applicant_contacts_2 (Full texts)
applicant_contacts_3 (Full texts)
applicant_address (Full texts)
applicant_email (Full texts)
applicant_nationality (Full texts)
applicant_civil_status (Full texts)
applicant_religion (Full texts)
applicant_languages (Full texts)
applicant_height (JSON type)
applicant_weight (JSON type)
applicant_position_type ('Household', 'Skilled')
applicant_preferred_position (Full texts)
currency (Full texts)
applicant_mothers (Full texts)
applicant_children (Full texts)
applicant_expected_salary (Full texts)
applicant_preferred_country (Full texts)
applicant_other_skills (Full texts)
personalAbilities (Full texts)
applicant_cv (Full texts)
applicant_photo (Full texts)
applicant_status (0 = 'Available', 1 = 'Cancelled', 2 = 'Reserved', ...)
sub_status (Full texts)
applicant_paid (Accounting Status, only admin can change this one)
applicant_employer (Full texts)
applicant_employer_number (Full texts)
applicant_job (Full texts)
applicant_source (Full texts)
applicant_incase_name (Full texts)
applicant_incase_relation (Full texts)
applicant_incase_contact (Full texts)
applicant_incase_address (Full texts)
is_repat (Full texts)
repat_date (Full texts)
other_source (Full texts)
applicant_slug (Full texts)
training_remarks (Full texts)
end_training_at (Full texts)
start_training_at (Full texts)
training_branches_id (Full texts)
optional_statuses_id (Full texts)
applicant_remarks (Full texts)
hit_id (Full texts)
hit_hearing_date (Full texts)
hit_status (Full texts)
hit_date (Full texts)
applicant_date_applied (Full texts)
applicant_createdby (Full texts)
applicant_updatedby (Full texts)
applicant_created (Full texts)
applicant_updated (Full texts)
applicant_fb (Full texts)
incc (Full texts)
singil (Full texts)
applicant_employer_address (Full texts)
applicant_date_interview (Full texts)
applicant_by_interview (Full texts)
agentcom (Full texts)
applicant_paid1 (Full texts)
applicant_ex (Full texts)
request1 (Full texts)
request2 (Full texts)
request3 (Full texts)
applicant_remarks_3 (Full texts)
applicant_employer_idno (Full texts)
applicant_remarks1 (Full texts)
numberone (Full texts)
applicant_jobs (Full texts)
timesched (Full texts)
passsched (Full texts)
releases (Full texts)
remarkspas (Full texts)
locsched (Full texts)
applicant_ppt_pay (Full texts)
applicant_ppt_stat (Full texts)
applicant_remarks5 (Full texts)
applicant_remarks6 (Full texts)
typess (Full texts)
highest1 (Full texts)
applicant_children1 (Full texts)
applicant_arabic (Full texts)
applicant_engslish (Full texts)
applicant_con (Full texts)
applicant_data1 (Full texts)
applicant_data2 (Full texts)
applicant_data3 (Full texts)
mystatus (Full texts)
hideme (Full texts)
selection_date (Full texts)
repat_date11 (Full texts)
accomodation1 (Full texts)
accomodation2 (Full texts)
accomodation3 (Full texts)
accomodation4 (Full texts)
accomodation5 (Full texts)
checkmet (Full texts)
pass_type (Full texts)
pass_com (Full texts)
locsched1 (Full texts)
userassign (Full texts)
typess1 (Full texts)
t1 (Full texts)
t2 (Full texts)
t3 (Full texts)
t4 (Full texts)
t5 (Full texts)
t6 (Full texts)
t7 (Full texts)
t8 (Full texts)
localflight2 (Full texts)
fb_link (Full texts)
applicant_remarks2 (Full texts)
applicant_remarks3 (Full texts)
singil1 (Full texts)
applicant_contacts_4 (Full texts)
`

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
        return `You are a Collaboration AI for a deployment agency system. Your goal is to clarify the user\'s needs and generate a precise initial context for subsequent AI agents. Keep your responses short and to the point. Engage in a natural language dialogue to deconstruct the request, ask targeted questions to resolve ambiguity, and confirm the scope, constraints, and desired output format. When you have a detailed context object, output it followed by the trigger [[COLLAB_DONE]].\n\nAvailable Database Schema:\n${dbSchema}`;
      case 'analyze':
        return `You are an Analysis AI for a deployment agency system. Your purpose is to summarize the user\'s intent and the clarified context into a concise brief for the Breakdown AI. You will receive a structured context object. Parse it, identify the core intent, key entities, and constraints, and formulate a high-level summary of the task to be performed.\n\nAvailable Database Schema:\n${dbSchema}`;
      // Add other roles as needed
      default:
        return `You are a helpful assistant for a deployment agency system. Keep your responses short and to the point.\n\nAvailable Database Schema:\n${dbSchema}`;
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