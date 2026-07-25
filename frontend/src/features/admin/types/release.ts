export interface Release {
  id: number;
  version: string;
  formatted_version: string;
  release_notes: string | null;
  release_notes_html?: string;
  released_at: string;
  is_current: boolean;
  created_by: {
    id: number;
    name: string;
  };
  created_at: string;
  updated_at: string;
}

export interface CurrentVersion {
  version: string;
  formatted_version: string;
  released_at: string;
  release_notes: string | null;
}

export interface ReleaseFormData {
  version: string;
  release_notes: string;
  released_at: string;
  is_current: boolean;
}