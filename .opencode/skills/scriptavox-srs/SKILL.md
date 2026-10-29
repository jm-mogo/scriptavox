---
name: scriptavox-srs
description: "Trigger: SRS, spaced repetition, review, again/hard/good, intervals. Conventions for ScriptaVox retention logic."
license: Apache-2.0
metadata:
  author: team
  version: "1.0"
---

## Activation Contract
Load when working on daily review, SRS scheduling, or recall assessment.

## Conventions
- Keep scheduling logic server-side where appropriate; follow existing patterns.
- Self-assessment: "Again", "Hard", "Good" tune next review intervals.
- Preserve user progress and review history.
- Add/adjust migrations carefully and document in ADR if changing core algorithm.
