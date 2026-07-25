import { useCurrentVersion } from '../hooks/useReleases';

export function VersionBadge() {
  const { version, loading } = useCurrentVersion();

  if (loading || !version) {
    return <span className="text-xs text-muted-foreground">v...</span>;
  }

  return (
    <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
      {version.formatted_version}
    </span>
  );
}