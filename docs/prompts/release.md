# Release Management Prompt — YFlow

## Purpose
Guide AI agents when managing releases and versioning.

---

## Version Format

Semantic Versioning: `MAJOR.MINOR.PATCH`

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes

---

## Release Checklist

### Pre-Release
- [ ] All tests passing
- [ ] Code coverage >= 80%
- [ ] Documentation updated
- [ ] Changelog written
- [ ] Version bumped in code

### Release Steps
1. Create release branch: `release/vX.Y.Z`
2. Final testing
3. Merge to main
4. Tag release: `git tag -a vX.Y.Z -m "Release vX.Y.Z"`
5. Push tags: `git push origin --tags`

### Post-Release
- [ ] Deploy to production
- [ ] Update API documentation
- [ ] Notify stakeholders
- [ ] Monitor for issues

---

## Changelog Format

```markdown
## [v1.0.0] - 2026-07-25

### Added
- New feature description

### Changed
- Changed feature description

### Fixed
- Bug fix description

### Deprecated
- Deprecated feature description

### Removed
- Removed feature description
```

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*