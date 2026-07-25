import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { useState } from 'react';

interface Notification {
  id: number;
  title: string;
  message: string;
  type: 'info' | 'success' | 'warning' | 'error';
  read: boolean;
  created_at: string;
}

const mockNotifications: Notification[] = [
  { id: 1, title: 'Task Assigned', message: 'You have been assigned to a new task', type: 'info', read: false, created_at: '2026-07-25T10:00:00' },
  { id: 2, title: 'Project Update', message: 'Project milestone completed', type: 'success', read: false, created_at: '2026-07-25T09:00:00' },
  { id: 3, title: 'Deadline Approaching', message: 'Task due in 24 hours', type: 'warning', read: true, created_at: '2026-07-24T18:00:00' },
];

export function NotificationsPage() {
  const [notifications, setNotifications] = useState(mockNotifications);

  const markAsRead = (id: number) => {
    setNotifications(notifications.map(n => n.id === id ? { ...n, read: true } : n));
  };

  const markAllAsRead = () => {
    setNotifications(notifications.map(n => ({ ...n, read: true })));
  };

  const unreadCount = notifications.filter(n => !n.read).length;

  const typeStyles: Record<string, string> = {
    info: 'border-l-blue-500 bg-blue-50',
    success: 'border-l-green-500 bg-green-50',
    warning: 'border-l-yellow-500 bg-yellow-50',
    error: 'border-l-red-500 bg-red-50',
  };

  return (
    <div className="p-6 space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Notifications</h1>
          <p className="text-sm text-muted-foreground">{unreadCount} unread</p>
        </div>
        {unreadCount > 0 && (
          <Button variant="outline" size="sm" onClick={markAllAsRead}>Mark all as read</Button>
        )}
      </div>

      <div className="space-y-2">
        {notifications.map(notification => (
          <Card
            key={notification.id}
            className={`p-4 border-l-4 ${typeStyles[notification.type]} ${!notification.read ? 'font-semibold' : ''}`}
          >
            <div className="flex items-start justify-between">
              <div className="flex-1">
                <h3 className="font-medium">{notification.title}</h3>
                <p className="text-sm text-muted-foreground mt-1">{notification.message}</p>
                <p className="text-xs text-muted-foreground mt-2">
                  {new Date(notification.created_at).toLocaleString()}
                </p>
              </div>
              {!notification.read && (
                <Button variant="ghost" size="sm" onClick={() => markAsRead(notification.id)}>
                  Mark read
                </Button>
              )}
            </div>
          </Card>
        ))}
      </div>

      {notifications.length === 0 && (
        <div className="text-center py-12 text-muted-foreground">No notifications</div>
      )}
    </div>
  );
}