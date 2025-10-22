import { bootstrapApplication } from '@angular/platform-browser';
import { appConfig } from './app/app.config';
import { AppComponent } from './app/app'; // Corrected import

bootstrapApplication(AppComponent, appConfig) // Corrected bootstrapping
  .catch((err) => console.error(err));
