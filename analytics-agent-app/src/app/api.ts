import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, from } from 'rxjs';
import { switchMap } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private phpApiUrl = '/agency/api/hello.php'; // Adjust this URL if your PHP endpoint changes
  private queryExecutorUrl = '/agency/api/query-executor.php';
  private baseApiKeyString = 'cyd';

  constructor(private http: HttpClient) { }

  private async generateDailyApiKey(): Promise<string> {
    const today = new Date();
    const year = today.getFullYear();
    const month = (today.getMonth() + 1).toString().padStart(2, '0');
    const day = today.getDate().toString().padStart(2, '0');
    const dateString = `${year}-${month}-${day}`;
    const combinedString = this.baseApiKeyString + dateString;

    const textEncoder = new TextEncoder();
    const data = textEncoder.encode(combinedString);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hexHash = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    return hexHash;
  }

  getHelloMessage(): Observable<{ message: string, timestamp: string }> {
    return this.http.get<{ message: string, timestamp: string }>(this.phpApiUrl);
  }

  getAiResponse(context: string | any[], message: string): Observable<any> {
    const aiServiceUrl = '/agency/api/ai-service.php';
    return this.http.post<any>(aiServiceUrl, { context, message });
  }

  executeQuery(sql: string, params: { type: string, value: any }[]): Observable<any> {
    return from(this.generateDailyApiKey()).pipe(
      switchMap(apiKey => {
        const headers = new HttpHeaders({
          'X-API-KEY': apiKey
        });
        return this.http.post<any>(this.queryExecutorUrl, { sql, params }, { headers });
      })
    );
  }
}