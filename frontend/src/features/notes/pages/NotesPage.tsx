import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useState } from 'react';

interface Note {
  id: number;
  title: string;
  content: string;
  created_at: string;
}

const mockNotes: Note[] = [
  { id: 1, title: 'Meeting Notes', content: 'Discussed project timeline and deliverables...', created_at: '2026-07-24' },
  { id: 2, title: 'Ideas', content: 'New feature suggestions for Q3...', created_at: '2026-07-23' },
];

export function NotesPage() {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedNote, setSelectedNote] = useState<Note | null>(null);
  const [showCreate, setShowCreate] = useState(false);
  const [newNoteTitle, setNewNoteTitle] = useState('');
  const [newNoteContent, setNewNoteContent] = useState('');

  const filteredNotes = mockNotes.filter(n =>
    n.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
    n.content.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="p-6 space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Knowledge Base</h1>
        <Button onClick={() => setShowCreate(true)}>New Note</Button>
      </div>

      <Input
        placeholder="Search notes..."
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.target.value)}
        className="max-w-sm"
      />

      {showCreate && (
        <Card className="p-4">
          <div className="space-y-3">
            <Input placeholder="Title" value={newNoteTitle} onChange={(e) => setNewNoteTitle(e.target.value)} />
            <textarea
              className="w-full p-2 border rounded-md min-h-[120px]"
              placeholder="Content"
              value={newNoteContent}
              onChange={(e) => setNewNoteContent(e.target.value)}
            />
            <div className="flex gap-2">
              <Button>Create</Button>
              <Button variant="outline" onClick={() => setShowCreate(false)}>Cancel</Button>
            </div>
          </div>
        </Card>
      )}

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {filteredNotes.map(note => (
          <Card
            key={note.id}
            className="p-4 cursor-pointer hover:shadow-lg transition"
            onClick={() => setSelectedNote(note)}
          >
            <h3 className="font-semibold mb-2">{note.title}</h3>
            <p className="text-sm text-muted-foreground line-clamp-3">{note.content}</p>
            <p className="text-xs text-muted-foreground mt-2">{new Date(note.created_at).toLocaleDateString()}</p>
          </Card>
        ))}
      </div>

      {selectedNote && (
        <Card className="p-6">
          <div className="flex items-start justify-between mb-4">
            <h2 className="text-xl font-bold">{selectedNote.title}</h2>
            <Button variant="ghost" size="sm" onClick={() => setSelectedNote(null)}>Close</Button>
          </div>
          <p className="whitespace-pre-wrap">{selectedNote.content}</p>
        </Card>
      )}

      {filteredNotes.length === 0 && (
        <div className="text-center py-12 text-muted-foreground">No notes found</div>
      )}
    </div>
  );
}