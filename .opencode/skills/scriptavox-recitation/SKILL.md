---
name: scriptavox-recitation
description: "Trigger: recite, speech, Web Speech API, diff, accuracy, practice. Conventions for ScriptaVox recitation feedback."
license: Apache-2.0
metadata:
  author: team
  version: "1.0"
---

## Activation Contract
Load when working on recitation/practice mode or speech feedback.

## Conventions
- Use Web Speech API carefully with browser compatibility in mind.
- Provide clear visual diff (correct/missed/added).
- Keep scoring logic transparent and testable.
- Handle microphone permissions gracefully.
