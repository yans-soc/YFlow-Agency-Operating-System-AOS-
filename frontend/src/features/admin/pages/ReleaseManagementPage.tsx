import { useState } from 'react';
import { useReleases } from '../hooks/useReleases';
import { ReleaseFormModal } from '../components/ReleaseFormModal';
import type { Release, ReleaseFormData } from '../types/release';

export function ReleaseManagementPage() {
  const { releases, loadingReleases, createRelease, updateRelease, deleteRelease, setCurrentRelease, updating, deleting, settingCurrent } = useReleases();
  const [showModal, setShowModal] = useState(false);
  const [editingRelease, setEditingRelease] = useState<Release | null>(null);

  const handleCreate = async (data: ReleaseFormData) => {
    await createRelease(data);
    setShowModal(false);
  };

  const handleUpdate = async (data: ReleaseFormData) => {
    if (!editingRelease) return;
    await updateRelease({ id: editingRelease.id, data });
    setEditingRelease(null);
    setShowModal(false);
  };

  const handleDelete = async (release: Release) => {
    if (!confirm(`Delete release ${release.version}?`)) return;
    await deleteRelease(release.id);
  };

  const handleSetCurrent = async (release: Release) => {
    if (!confirm(`Set ${release.version} as current version?`)) return;
    await setCurrentRelease(release.id);
  };

  const openEditModal = (release: Release) => {
    setEditingRelease(release);
    setShowModal(true);
  };

  const openCreateModal = () => {
    setEditingRelease(null);
    setShowModal(true);
  };

  if (loadingReleases) {
    return <div className="p-6">Loading releases...</div>;
  }

  return (
    <div className="p-6 space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold">Release Management</h1>
          <p className="text-muted-foreground">Manage application versions and changelogs</p>
        </div>
        <button
          onClick={openCreateModal}
          className="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90"
        >
          New Release
        </button>
      </div>

      <div className="border rounded-lg overflow-hidden">
        <table className="w-full">
          <thead className="bg-muted">
            <tr>
              <th className="text-left p-3">Version</th>
              <th className="text-left p-3">Release Date</th>
              <th className="text-left p-3">Status</th>
              <th className="text-left p-3">Created By</th>
              <th className="text-right p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            {releases?.data.map((release) => (
              <tr key={release.id} className="border-t">
                <td className="p-3">
                  <span className="font-mono">{release.formatted_version}</span>
                </td>
                <td className="p-3">{new Date(release.released_at).toLocaleDateString()}</td>
                <td className="p-3">
                  {release.is_current ? (
                    <span className="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Current</span>
                  ) : (
                    <span className="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Archived</span>
                  )}
                </td>
                <td className="p-3">{release.created_by.name}</td>
                <td className="p-3 text-right space-x-2">
                  {!release.is_current && (
                    <button
                      onClick={() => handleSetCurrent(release)}
                      disabled={settingCurrent}
                      className="text-sm text-blue-600 hover:underline disabled:opacity-50"
                    >
                      Set Current
                    </button>
                  )}
                  <button
                    onClick={() => openEditModal(release)}
                    className="text-sm text-gray-600 hover:underline"
                  >
                    Edit
                  </button>
                  {!release.is_current && (
                    <button
                      onClick={() => handleDelete(release)}
                      disabled={deleting}
                      className="text-sm text-red-600 hover:underline disabled:opacity-50"
                    >
                      Delete
                    </button>
                  )}
                </td>
              </tr>
            ))}
            {(!releases || releases.data.length === 0) && (
              <tr>
                <td colSpan={5} className="p-6 text-center text-muted-foreground">
                  No releases found. Create your first release.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {showModal && (
        <ReleaseFormModal
          release={editingRelease}
          onSubmit={editingRelease ? handleUpdate : handleCreate}
          onClose={() => {
            setShowModal(false);
            setEditingRelease(null);
          }}
          loading={updating}
        />
      )}
    </div>
  );
}