namespace App\Services;

use Illuminate\Support\Facades\Http;

class LineNotifyService
{

    public function sendNotify($message, $token)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('https://notify-api.line.me/api/notify', [
            'message' => $message
        ]);

        return $response->json();
    }
}
