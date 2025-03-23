<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Системное уведомление
 */
class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Текст уведомления
     *
     * @var string
     */
    protected string $message;

    /**
     * Тип уведомления (info, warning, error, success)
     *
     * @var string
     */
    protected string $type;

    /**
     * Дополнительные данные
     *
     * @var array
     */
    protected array $data;

    /**
     * Создать новый экземпляр уведомления.
     *
     * @param string $message
     * @param string $type
     * @param array $data
     * @return void
     */
    public function __construct(string $message, string $type = 'info', array $data = [])
    {
        $this->message = $message;
        $this->type = in_array($type, ['info', 'warning', 'error', 'success']) ? $type : 'info';
        $this->data = $data;
    }

    /**
     * Получить каналы доставки уведомления.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Получить представление письма для уведомления.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Системное уведомление')
            ->line($this->message)
            ->line('Спасибо за использование нашего приложения!');
    }

    /**
     * Получить массив для сохранения в базе данных.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'data' => $this->data,
            'timestamp' => now(),
        ];
    }
} 