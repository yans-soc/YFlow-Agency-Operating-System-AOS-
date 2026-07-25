import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useState } from 'react';

interface File {
  id: number;
  name: string;
  type: string;
  size: number;
  uploaded_by: string;
  created_at: string;
}

const mockFiles: File[] = [
  { id: 1, name: 'project-brief.pdf', type: 'PDF', size: 2048576, uploaded_by: 'John Doe', created_at: '2026-07-24' },
  { id: 2, name: 'design-mockups.fig', type: 'FIG', size: 15728640, uploaded_by: 'Jane Smith', created_at: '2026-07-23' },
  { id: 3, name: 'meeting-notes.docx', type: 'DOCX', size: 524288, uploaded_by: 'Bob Wilson', created_at: '2026-07-22' },
];

const formatSize = (bytes: number) => {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / 1048576).toFixed(1) + ' MB';
};

export function FilesPage() {
  const [searchTerm, setSearchTerm] = useState('');

  const filteredFiles = mockFiles.filter(f =>
    f.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="p-6 space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Files</h1>
        <Button>Upload File</Button>
      </div>

      <Input
        placeholder="Search files..."
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.target.value)}
        className="max-w-sm"
      />

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {filteredFiles.map(file => (
          <Card key={file.id} className="p-4">
            <div className="flex items-start gap-3">
              <div className="w-12 h-12 rounded-lg bg-secondary flex items-center justify-center font-semibold text-sm">
                {file.type}
              </div>
              <div className="flex-1 min-w-0">
                <h3 className="font-medium truncate">{file.name}</h3>
                <p className="text-sm text-muted-foreground">{formatSize(file.size)}</p>
                <p className="text-xs text-muted-foreground mt-1">By {file.uploaded_by}</p>
                <p className="text-xs text-muted-foreground">
                  {new Date(file.created_at).toLocaleDateString()}
                </p>
              </div>
            </div>
            <div className="flex gap-2 mt-3 pt-3 border-t">
              <Button variant="outline" size="sm" className="flex-1">Download</Button>
              <Button variant="outline" size="sm">Delete</Button>
            </div>
          </Card>
        ))}
      </div>

      {filteredFiles.length === 0 && (
        <div className="text-center py-12 text-muted-foreground">No files found</div>
      )}
    </div>
  );
}