# Release Checklist Template

## Pre-Release (1 week before)
- [ ] Feature freeze confirmed
- [ ] All tests passing
- [ ] Code coverage >= 80%
- [ ] Documentation updated
- [ ] Changelog drafted
- [ ] Version number determined

## Release Candidate
- [ ] Create release branch: `release/vX.Y.Z`
- [ ] Run full test suite
- [ ] QA sign-off
- [ ] Security review complete
- [ ] Performance benchmarks run
- [ ] Stakeholder approval

## Release Day
- [ ] Merge to main
- [ ] Tag release: `git tag -a vX.Y.Z`
- [ ] Push tags
- [ ] Deploy to production
- [ ] Verify deployment
- [ ] Update API docs
- [ ] Notify stakeholders

## Post-Release
- [ ] Monitor for 24 hours
- [ ] Address any critical issues
- [ ] Update release notes
- [ ] Archive release branch
- [ ] Retrospective scheduled

---

**Release Version:** vX.Y.Z  
**Release Date:** YYYY-MM-DD  
**Release Manager:** [Name]