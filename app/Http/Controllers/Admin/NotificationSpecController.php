<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationSpecService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationSpecController extends Controller
{
    public function __construct(
        private readonly NotificationSpecService $service,
    ) {
    }

    /**
     * 通知・リマインダー・未済タスクの仕様確認／変更画面
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'notifications');
        if (!in_array($tab, ['notifications', 'reminders', 'tasks'], true)) {
            $tab = 'notifications';
        }

        return view('admin.notification_spec.index', [
            'tab' => $tab,
            'notificationsByGroup' => $this->service->notificationsForView(),
            'remindersByGroup' => $this->service->remindersForView(),
            'tasksByActor' => $this->service->tasksForView(),
            'unitLabel' => fn (string $u) => $this->service->unitLabel($u),
        ]);
    }

    /**
     * 通知（ON/OFF + 文章）の更新
     */
    public function updateNotification(Request $request, string $key): RedirectResponse
    {
        $request->validate([
            'enabled' => ['nullable'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $catalog = collect($this->service->notificationCatalog())->keyBy('key');
        abort_unless($catalog->has($key), 404);

        $this->service->saveSetting(NotificationSpecService::TYPE_NOTIFICATION, $key, [
            'enabled' => (bool) $request->boolean('enabled'),
            'title' => (string) $request->input('title', ''),
            'body' => (string) $request->input('body', ''),
        ]);

        return redirect()->route('admin.notification-spec.index', ['tab' => 'notifications', '_anchor' => $key])
            ->with('status', '通知設定を更新しました：' . $catalog->get($key)['label']);
    }

    /**
     * リマインダー（offset + 文章）の更新
     */
    public function updateReminder(Request $request, string $key): RedirectResponse
    {
        $request->validate([
            'offset' => ['required', 'integer', 'min:0', 'max:9999'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $catalog = collect($this->service->reminderCatalog())->keyBy('key');
        abort_unless($catalog->has($key), 404);

        $this->service->saveSetting(NotificationSpecService::TYPE_REMINDER, $key, [
            'offset' => (int) $request->input('offset'),
            'title' => (string) $request->input('title', ''),
            'body' => (string) $request->input('body', ''),
        ]);

        return redirect()->route('admin.notification-spec.index', ['tab' => 'reminders', '_anchor' => $key])
            ->with('status', 'リマインダーを更新しました：' . $catalog->get($key)['label']);
    }

    /**
     * 未済タスク（表示文言のみ）更新
     */
    public function updateTask(Request $request, string $key): RedirectResponse
    {
        $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $catalog = collect($this->service->taskCatalog())->keyBy('key');
        abort_unless($catalog->has($key), 404);

        $this->service->saveSetting(NotificationSpecService::TYPE_TASK, $key, [
            'title' => (string) $request->input('title', ''),
            'body' => (string) $request->input('body', ''),
        ]);

        return redirect()->route('admin.notification-spec.index', ['tab' => 'tasks', '_anchor' => $key])
            ->with('status', 'タスク表示文言を更新しました：' . $catalog->get($key)['label']);
    }
}
