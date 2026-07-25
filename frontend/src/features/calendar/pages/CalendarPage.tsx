import { useState } from 'react';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { CalendarView, CalendarEvent } from '../types/calendar';

const mockEvents: CalendarEvent[] = [
  { id: 1, title: 'Task Review', start_time: '2026-07-25T10:00:00', end_time: '2026-07-25T11:00:00', all_day: false, type: 'meeting', created_at: '2026-07-24T00:00:00' },
  { id: 2, title: 'Project Deadline', start_time: '2026-07-26T00:00:00', end_time: '2026-07-26T23:59:59', all_day: true, type: 'deadline', created_at: '2026-07-24T00:00:00' },
];

export function CalendarPage() {
  const [view, setView] = useState<CalendarView>('month');
  const [currentDate, setCurrentDate] = useState(new Date());

  const daysInMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();
  const firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).getDay();

  const getEventsForDay = (day: number) => {
    const dateStr = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return mockEvents.filter(e => e.start_time.startsWith(dateStr));
  };

  const prevMonth = () => setCurrentDate(new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1));
  const nextMonth = () => setCurrentDate(new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1));
  const today = () => setCurrentDate(new Date());

  return (
    <div className="p-6 space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Calendar</h1>
        <div className="flex gap-2">
          <Button variant="outline" size="sm" onClick={prevMonth}>←</Button>
          <span className="px-4 py-2 font-semibold">
            {currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}
          </span>
          <Button variant="outline" size="sm" onClick={nextMonth}>→</Button>
          <Button variant="outline" size="sm" onClick={today}>Today</Button>
        </div>
      </div>

      <div className="flex gap-2">
        {(['month', 'week', 'day', 'agenda'] as CalendarView[]).map((v) => (
          <Button key={v} variant={view === v ? 'default' : 'outline'} size="sm" onClick={() => setView(v)}>
            {v.charAt(0).toUpperCase() + v.slice(1)}
          </Button>
        ))}
      </div>

      <Card className="p-4">
        <div className="grid grid-cols-7 gap-1 mb-2">
          {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(d => (
            <div key={d} className="text-center text-sm font-semibold p-2">{d}</div>
          ))}
        </div>
        <div className="grid grid-cols-7 gap-1">
          {Array.from({ length: firstDayOfMonth }).map((_, i) => (
            <div key={`empty-${i}`} className="aspect-square" />
          ))}
          {Array.from({ length: daysInMonth }).map((_, i) => {
            const day = i + 1;
            const events = getEventsForDay(day);
            const isToday = day === new Date().getDate() && 
              currentDate.getMonth() === new Date().getMonth() && 
              currentDate.getFullYear() === new Date().getFullYear();
            
            return (
              <div key={day} className={`aspect-square border p-1 ${isToday ? 'bg-primary/10' : ''}`}>
                <div className="text-sm font-medium">{day}</div>
                <div className="space-y-1 mt-1">
                  {events.slice(0, 2).map(e => (
                    <div key={e.id} className={`text-xs px-1 py-0.5 rounded truncate ${
                      e.type === 'deadline' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'
                    }`}>
                      {e.title}
                    </div>
                  ))}
                  {events.length > 2 && (
                    <div className="text-xs text-muted-foreground">+{events.length - 2} more</div>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      </Card>
    </div>
  );
}