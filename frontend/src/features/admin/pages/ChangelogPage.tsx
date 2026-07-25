import { useReleases } from '../hooks/useReleases';

export function ChangelogPage() {
  const { releases, loadingReleases } = useReleases();

  if (loadingReleases) {
    return <div className="p-6">Loading changelog...</div>;
  }

  return (
    <div className="p-6 max-w-4xl mx-auto">
      <h1 className="text-2xl font-bold mb-6">Changelog</h1>

      <div className="space-y-8">
        {releases?.data.map((release) => (
          <div key={release.id} className="border-l-2 pl-6 pb-8 relative">
            <div className="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-primary" />
            
            <div className="mb-2">
              <div className="flex items-center gap-3 flex-wrap">
                <span className="font-mono text-lg font-semibold">{release.formatted_version}</span>
                {release.is_current && (
                  <span className="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded">Current</span>
                )}
                <span className="text-sm text-muted-foreground">
                  {new Date(release.released_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                  })}
                </span>
              </div>
            </div>

            {release.release_notes && (
              <div className="prose prose-sm max-w-none">
                <pre className="whitespace-pre-wrap font-sans text-sm text-muted-foreground bg-muted p-4 rounded-md">
                  {release.release_notes}
                </pre>
              </div>
            )}

            <div className="text-xs text-muted-foreground mt-2">
              Released by {release.created_by.name}
            </div>
          </div>
        ))}

        {(!releases || releases.data.length === 0) && (
          <div className="text-center text-muted-foreground py-12">
            No releases yet.
          </div>
        )}
      </div>
    </div>
  );
}