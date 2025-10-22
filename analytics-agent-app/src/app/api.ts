import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private phpApiUrl = '/agency/api/hello.php'; // Adjust this URL if your PHP endpoint changes
  private queryExecutorUrl = '/agency/api/query-executor.php';

  constructor(private http: HttpClient) { }

  getHelloMessage(): Observable<{ message: string, timestamp: string }> {
    return this.http.get<{ message: string, timestamp: string }>(this.phpApiUrl);
  }

  getAiResponse(context: string | any[], message: string): Observable<any> {
    const aiServiceUrl = '/agency/api/ai-service.php';
    return this.http.post<any>(aiServiceUrl, { context, message });
  }

  executeQuery(sql: string, params: { type: string, value: any }[]): Observable<any> {
    return this.http.post<any>(this.queryExecutorUrl, { sql, params });
  }
}