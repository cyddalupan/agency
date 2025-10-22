import { bootstrapApplication } from '@angular/platform-browser';
import { appConfig } from './app/app.config';
import { AppComponent } from './app/app';

import 'zone.js'; // <--- Add this line to import Zone.js

bootstrapApplication(AppComponent, appConfig)
  .catch((err) => console.error(err));
