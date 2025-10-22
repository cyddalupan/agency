import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
// import { RouterOutlet } from '@angular/router'; // Removed RouterOutlet as it's not used
import { ApiService } from './api'; // Corrected import path: removed .ts extension

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule], // Removed RouterOutlet
  template: `
    <h1>Angular App</h1>
    <p>Message from PHP: {{ phpMessage }}</p>
    <p>Timestamp: {{ phpTimestamp }}</p>
  `,
  styleUrl: './app.css'
})
export class AppComponent implements OnInit {
  title = 'analytics-agent';
  phpMessage: string = '';
  phpTimestamp: string = '';

  constructor(private apiService: ApiService) {}

  ngOnInit(): void {
    this.apiService.getHelloMessage().subscribe(data => {
      this.phpMessage = data.message;
      this.phpTimestamp = data.timestamp;
    });
  }
}