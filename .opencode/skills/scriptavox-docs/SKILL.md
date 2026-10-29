---
name: scriptavox-docs
description: "Trigger: docs, documentation, ADR, task log, README index. Enforce the docs/ structure for ScriptaVox."
license: Apache-2.0
metadata:
  author: team
  version: "1.0"
---

## Activation Contract
Load when creating/updating docs under `docs/` or when updating `README.md` to link docs.

## Rules
1. Single entry point: `docs/README.md` must link to everything. Update it on every new doc.
2. Docs live in-repo and are updated in the same commit as code changes.
3. Link, don't duplicate.
4. Use kebab-case filenames, ISO dates (YYYY-MM-DD) for time-based files.
5. ADRs: never edit existing ADRs; create a new one that supersedes (`Status: Replaced by 000N`).
6. Keep docs short and single-purpose.
7. Follow the structure: architecture/, features/, operations/, decisions/, tasks/, _templates/.

## Workflow
- Add new ADR to `docs/decisions/` as `NNNN-title.md` using `_templates/decision.md`.
- Add task log to `docs/tasks/` as `YYYY-MM-DD-title.md` using `_templates/task.md`.
- Update `docs/README.md` index.
- If behavior changes, update relevant docs in the same change.
