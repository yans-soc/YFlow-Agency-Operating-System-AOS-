import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Release, ReleaseFormData } from '../types/release';

interface Props {
  release?: Release | null;
  onSubmit: (data: ReleaseFormData) => Promise<void>;
  onClose: () => void;
  loading?: boolean;
}

export function ReleaseFormModal({ release, onSubmit, onClose, loading }: Props) {
  const { register, handleSubmit, formState: { errors } } = useForm<ReleaseFormData>({
    defaultValues: {
      version: release?.version || '',
      release_notes: release?.release_notes || '',
      released_at: release?.released_at || new Date().toISOString().split('T')[0],
      is_current: release?.is_current || false,
    },
  });

  const [submitError, setSubmitError] = useState<string>('');

  const handleFormSubmit = async (data: ReleaseFormData) => {
    setSubmitError('');
    try {
      await onSubmit(data);
      onClose();
    } catch (err) {
      setSubmitError(err instanceof Error ? err.message : 'Failed to save release');
    }
  };

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-background rounded-lg p-6 w-full max-w-md">
        <h2 className="text-xl font-semibold mb-4">
          {release ? 'Edit Release' : 'New Release'}
        </h2>

        <form onSubmit={handleSubmit(handleFormSubmit)} className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-1">Version</label>
            <Input
              {...register('version', {
                required: 'Version is required',
                pattern: {
                  value: /^\d+\.\d+\.\d+(-[a-z0-9]+)?$/,
                  message: 'Format: X.Y.Z or X.Y.Z-prerelease',
                },
              })}
              placeholder="1.0.0"
            />
            {errors.version && (
              <p className="text-sm text-destructive mt-1">{errors.version.message}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium mb-1">Release Notes</label>
            <textarea
              {...register('release_notes')}
              className="w-full min-h-[120px] px-3 py-2 border rounded-md text-sm"
              placeholder="Describe changes in this release..."
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-1">Release Date</label>
            <Input
              type="date"
              {...register('released_at', { required: 'Date is required' })}
            />
            {errors.released_at && (
              <p className="text-sm text-destructive mt-1">{errors.released_at.message}</p>
            )}
          </div>

          <div className="flex items-center gap-2">
            <input
              type="checkbox"
              id="is_current"
              {...register('is_current')}
              className="rounded"
            />
            <label htmlFor="is_current" className="text-sm">Set as current version</label>
          </div>

          {submitError && (
            <p className="text-sm text-destructive">{submitError}</p>
          )}

          <div className="flex gap-2 justify-end pt-4">
            <Button type="button" variant="outline" onClick={onClose} disabled={loading}>
              Cancel
            </Button>
            <Button type="submit" disabled={loading}>
              {loading ? 'Saving...' : 'Save'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}