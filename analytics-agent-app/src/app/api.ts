import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, from, throwError, timer } from 'rxjs'; // Added throwError, timer
import { switchMap, retryWhen, delay, take, concatMap } from 'rxjs/operators'; // Added retryWhen, delay, take, concatMap

// Helper function for retrying with a delay
const retryWithDelay = (delayMs: number, maxRetries: number = 1) => { // maxRetries set to 1 for a single retry
  return (errors: Observable<any>) => {
    return errors.pipe(
      concatMap((error, iteration) => {
        if (iteration < maxRetries) {
          console.warn(`API call failed, retrying in ${delayMs / 1000} seconds... (Attempt ${iteration + 1}/${maxRetries})`, error);
          return timer(delayMs);
        } else {
          return throwError(() => new Error(`API call failed after ${maxRetries} retries.`));
        }
      })
    );
  };
};

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
    const year = today.getUTCFullYear();
    const month = (today.getUTCMonth() + 1).toString().padStart(2, '0');
    const day = today.getUTCDate().toString().padStart(2, '0');
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
    return this.http.get<{ message: string, timestamp: string }>(this.phpApiUrl).pipe(
      retryWhen(retryWithDelay(5000))
    );
  }

  getAiResponse(context: string | any[], message: string, role: string = 'collaborate'): Observable<any> {
    const aiServiceUrl = '/agency/api/ai-service.php';
    return from(this.generateDailyApiKey()).pipe(
      switchMap(apiKey => {
        const headers = new HttpHeaders({
          'X-API-KEY': apiKey
        });
        return this.http.post<any>(aiServiceUrl, { context, message, role }, { headers }).pipe(
          retryWhen(retryWithDelay(5000))
        );
      })
    );
  }

  executeQuery(sql: string, params: { type: string, value: any }[]): Observable<any> {
    return from(this.generateDailyApiKey()).pipe(
      switchMap(apiKey => {
        const headers = new HttpHeaders({
          'X-API-KEY': apiKey
        });
        return this.http.post<any>(this.queryExecutorUrl, { sql, params }, { headers }).pipe(
          retryWhen(retryWithDelay(5000))
        );
      })
    );
  }

  saveChatHistory(message: string, reply: string): Observable<any> {
    const sql = `INSERT INTO chat_history (message, reply, timestamp) VALUES (?, ?, NOW())`;
    const params = [
      { type: 's', value: message },
      { type: 's', value: reply }
    ];
    return this.executeQuery(sql, params);
  }

  getChatHistory(): Observable<any> {
    const sql = `SELECT message, reply FROM chat_history ORDER BY timestamp DESC LIMIT 15`;
    return this.executeQuery(sql, []);
  }
}