# 07 · AI Architecture

AI is a layer, not the product. It (a) increases patient conversion & confidence, (b) reduces ops load, (c) scales SEO/content, (d) improves matching quality. **Guardrail:** AI never gives definitive medical diagnosis or replaces a dentist — it estimates, guides, and routes to real clinicians, with disclaimers. Every patient-facing medical output is framed as informational + "confirm with a dentist."

## 1. Platform design

```
                 ┌───────────────────────────────────────────┐
 App (Livewire)  │            AiService (facade)             │
  & Jobs  ─────► │  provider-agnostic, prompt registry,      │ ─► LLM providers (Claude primary;
                 │  function/tool calling, streaming,         │    pluggable), embeddings, moderation
                 │  caching, cost/rate limits, PII redaction, │
                 │  logging, eval/guardrails                  │ ─► Vector store (pgvector/Meili/Qdrant)
                 └───────────────────────────────────────────┘
```

Design decisions:
- **Provider-agnostic driver** (`config/ai.php`) so models can be swapped/upgraded; default to the latest capable Claude models for reasoning/writing.
- **Prompt registry** — versioned prompt templates in `app/Ai/Prompts` (or DB `ai_prompts` for editable ones), with variables, guardrail preamble, and eval fixtures.
- **RAG** over first-party knowledge: treatments, prices, clinic/doctor data, approved FAQs, policies → embeddings in a vector store. Answers are grounded in our data (reduces hallucination, keeps facts current & on-brand).
- **Tool/function calling** lets AI query structured data (get_price, find_clinics, estimate_cost, get_faq) instead of guessing.
- **PII redaction + consent** before sending lead data to any model; medical images processed under strict policy; log prompts/responses (redacted) for audit & eval.
- **Async where possible** via queued jobs (content, summaries, translations); **streaming** for chat/advisor UX.
- **Cost controls:** per-feature token budgets, response caching (esp. review summaries, FAQ generation), rate limiting, fallbacks to templated responses if AI unavailable.
- **Human-in-the-loop** for anything published or medical: draft → review → approve.

## 2. Feature specs

### 2.1 AI Treatment Advisor (patient-facing, conversational)
- **Goal:** guide undecided visitors to a likely treatment + cost band + matched clinics → into the lead funnel.
- **Flow:** structured intake (symptom/goal, missing teeth, photos optional) → clarifying questions → RAG-grounded recommendation with confidence + "confirm with a dentist" disclaimer → 3 matched clinics → CTA to full quote (pre-fills lead form).
- **Tech:** streaming chat (Livewire + Alpine), tool-calls to treatment/price/clinic data, session stored, converts to `lead` (channel=`ai_advisor`). Never diagnoses; flags emergencies → emergency page/contact.

### 2.2 AI Clinic Recommendation Engine (matching)
- **Goal:** rank best clinics for a given lead — powering both advisor and CRM assignment.
- **Signals:** treatment match, price fit vs budget, verification tier, language match, rating, response time, capacity/availability, location prefs, historical conversion/outcome for similar leads.
- **Tech:** hybrid — deterministic scoring + rules (must-haves) with an ML/embedding re-rank layer; explainable ("matched because: speaks German, does All-on-4, in budget, ✓Elite, replies <2h"). Feeds `lead_assignments`. Improves via feedback loop (won/lost outcomes).

### 2.3 AI Cost Estimator
- **Goal:** instant, honest price band from treatment + count + country + optional photos.
- **Tech:** rules/data-driven (from `clinic_treatment` + `country_treatment` price data) with AI for parsing free-text/photos into structured inputs. Always a *range* + "final price after clinic review of your case." Shown at lead confirmation & on cost pages.

### 2.4 AI Chat Assistant (support / concierge)
- **Goal:** 24/7 answers on process, safety, travel, guarantees; deflect FAQs; capture leads; escalate to human/WhatsApp.
- **Tech:** RAG over FAQs/policies/guides; tool-calls for clinic/price lookups; hand-off to CRM (creates `messages`/`lead`), multilingual, with clear "AI assistant" labeling and human-escalation button.

### 2.5 AI Review Summary
- **Goal:** synthesize verified reviews into themes ("Pros: English-speaking staff, painless; Watch: parking") + overall sentiment.
- **Tech:** batched job over approved reviews per clinic/doctor; cached in `ai_summary_cache`/`reviews.ai_summary_cache`; regenerated on new reviews. Extractive + honest, no invented praise; shows on clinic/review pages.

### 2.6 AI Medical FAQ Generator (content assist)
- **Goal:** draft treatment/country/clinic FAQs from real questions (search queries, lead messages, support logs).
- **Tech:** generates draft Q&A grounded in our data + sources → **human/dentist review** → publish to `faqs` (+ FAQ schema). Never auto-publishes medical claims.

### 2.7 AI SEO Content Assistant (internal)
- **Goal:** scale content safely — briefs, outlines, drafts, meta titles/descriptions, internal-link suggestions, schema suggestions, keyword-gap analysis.
- **Tech:** takes `keyword_map` gaps + SERP/GSC data → brief → draft (grounded, cited) → editor + medical reviewer → publish. Enforces EEAT template (author, reviewer, sources, dates). Flags thin/duplicate before publish. Assists, never fully automates YMYL publishing.

### 2.8 AI Translation System
- **Goal:** scale to unlimited languages while preserving medical accuracy, tone, and SEO.
- **Tech:** queued translation jobs per translatable field; glossary/termbase (treatment names, brand terms) for consistency; locale-aware; **native/professional review** for primary markets before publish; auto-fills `translations`/translatable JSON; regenerates hreflang. Detects source changes → re-translate flag. Keeps currency/units localization separate from language.

## 3. Data & infra
- **Vector store:** pgvector (if Postgres added) or Qdrant/Meili hybrid; embeddings for clinics, doctors, treatments, posts, FAQs, reviews → powers RAG, related content, semantic search, matching re-rank.
- **Queues (Redis/Horizon):** `ai-content`, `ai-translation`, `ai-summary`, `ai-embeddings`, `ai-realtime` (higher priority for chat).
- **Storage:** prompts/responses (redacted) + evals in `ai_runs` table for audit, cost tracking, quality monitoring.
- **Moderation:** input/output moderation pass on user-facing AI; block unsafe/medical-overreach; profanity/PII filters.

## 4. Governance, safety & quality
- **Guardrail preamble** on every patient-facing prompt: informational only, no diagnosis, encourage professional consult, escalate emergencies, stay grounded in provided data, refuse out-of-scope.
- **Grounding-first:** prefer RAG facts; if unknown, say so + route to human — never fabricate prices, credentials, or outcomes.
- **Human review gates** for: published content, FAQs, translations (primary markets), any medical statement.
- **Evals:** golden-set tests per feature (accuracy of cost bands, matching relevance, summary faithfulness, translation adequacy) run in CI + monitored in prod; drift alerts.
- **Transparency:** AI features clearly labeled; disclaimers; easy human hand-off; log + allow audit of AI decisions affecting patients (matching).
- **Privacy:** consent before processing PII/medical images; configurable no-train/zero-retention with providers; regional data handling per GDPR.
- **Cost/rate:** per-feature budgets, caching, graceful degradation to non-AI fallbacks.

## 5. Provider config sketch
```php
// config/ai.php
'default' => env('AI_PROVIDER','claude'),
'providers' => [
  'claude' => ['model' => 'claude-opus-4-8', 'fast_model' => 'claude-haiku-4-5-20251001', ...],
],
'embeddings' => ['driver' => 'openai_or_provider', 'dim' => 1536],
'features' => [
  'advisor'   => ['model'=>'reasoning','stream'=>true,'budget_tokens'=>...,'guardrail'=>'medical'],
  'summary'   => ['model'=>'fast','cache'=>true],
  'translate' => ['model'=>'reasoning','review_required'=>['en','de','tr']],
  // ...
],
```
Keep model IDs in config; when building, default to the latest, most capable Claude models and a fast model for high-volume/low-stakes tasks (summaries, classification).
