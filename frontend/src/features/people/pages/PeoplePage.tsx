import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useState } from 'react';

interface Person {
  id: number;
  name: string;
  email: string;
  position?: string;
  department?: string;
  skills?: string[];
}

const mockPeople: Person[] = [
  { id: 1, name: 'John Doe', email: 'john@example.com', position: 'Developer', department: 'Engineering', skills: ['React', 'Node.js'] },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', position: 'Designer', department: 'Design', skills: ['Figma', 'UI/UX'] },
  { id: 3, name: 'Bob Wilson', email: 'bob@example.com', position: 'Manager', department: 'Operations', skills: ['Leadership', 'Agile'] },
];

export function PeoplePage() {
  const [searchTerm, setSearchTerm] = useState('');

  const filteredPeople = mockPeople.filter(p =>
    p.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    p.email.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="p-6 space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">People</h1>
        <Button>Add Person</Button>
      </div>

      <Input
        placeholder="Search people..."
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.target.value)}
        className="max-w-sm"
      />

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {filteredPeople.map(person => (
          <Card key={person.id} className="p-4">
            <div className="flex items-start gap-3">
              <div className="w-12 h-12 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-lg font-semibold">
                {person.name.charAt(0)}
              </div>
              <div className="flex-1">
                <h3 className="font-semibold">{person.name}</h3>
                <p className="text-sm text-muted-foreground">{person.position}</p>
                <p className="text-xs text-muted-foreground">{person.department}</p>
                <p className="text-xs text-muted-foreground mt-1">{person.email}</p>
                {person.skills && person.skills.length > 0 && (
                  <div className="flex flex-wrap gap-1 mt-2">
                    {person.skills.map(skill => (
                      <span key={skill} className="text-xs px-2 py-0.5 bg-secondary rounded">
                        {skill}
                      </span>
                    ))}
                  </div>
                )}
              </div>
            </div>
          </Card>
        ))}
      </div>

      {filteredPeople.length === 0 && (
        <div className="text-center py-12 text-muted-foreground">No people found</div>
      )}
    </div>
  );
}