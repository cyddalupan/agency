import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
// import { RouterOutlet } from '@angular/router'; // Removed RouterOutlet as it's not used
import { ApiService } from './api'; // Corrected import path: removed .ts extension

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule], // Removed RouterOutlet
  template: `
    <div class="min-h-screen flex items-center justify-center p-4">
      <div class="glass-container w-full max-w-md p-8 rounded-xl shadow-2xl text-white">
        <h1 class="text-4xl font-bold mb-6 text-center">Liquid Glass App</h1>

        <div class="mb-6">
          <p class="text-lg mb-2">Welcome to your new Angular application with a stunning liquid glass design!</p>
          <p class="text-sm opacity-80">This is a demonstration of how the glassmorphism effect can be applied to various UI elements.</p>
        </div>

        <div class="mb-6 p-4 rounded-lg glass-card">
          <h2 class="text-2xl font-semibold mb-3">PHP API Data</h2>
          <p class="mb-1">Message: <span class="font-medium">{{ phpMessage }}</span></p>
          <p>Timestamp: <span class="font-medium">{{ phpTimestamp }}</span></p>
        </div>

        <div class="mb-6">
          <label for="name" class="block text-sm font-medium mb-2">Your Name</label>
          <input type="text" id="name" placeholder="Enter your name"
                 class="w-full p-3 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:ring-2 focus:ring-blue-400 glass-input">
        </div>

        <button class="w-full py-3 px-4 rounded-lg bg-blue-500/30 hover:bg-blue-600/40 transition-colors duration-200 text-lg font-semibold glass-button">
          Submit
        </button>
      </div>
    </div>
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