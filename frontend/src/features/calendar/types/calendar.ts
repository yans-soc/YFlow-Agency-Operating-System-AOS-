export type CalendarView = 'month' | 'week' | 'day' | 'agenda';

export interface CalendarEvent {
  id: number;
  title: string;
  description?: string;
  start_time: string;
  end_time: string;
  all_day: boolean;
  type: 'task' | 'meeting' | 'deadline' | 'reminder';
  related_id?: number;
  created_at: string;
}