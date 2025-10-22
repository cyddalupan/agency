import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private phpApiUrl = '/agency/api/hello.php'; // Adjust this URL if your PHP endpoint changes

  constructor(private http: HttpClient) { }

  getHelloMessage(): Observable<{ message: string, timestamp: string }> {
    return this.http.get<{ message: string, timestamp: string }>(this.phpApiUrl);
  }
}