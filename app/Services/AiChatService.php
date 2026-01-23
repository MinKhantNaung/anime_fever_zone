<?php

namespace App\Services;

use Prism\Prism\Facades\Prism;

final class AiChatService
{
    public function respond(string $message, array $history = []): string
    {
        $contextLines = $this->buildContextLines($history);
        $prompt = $this->buildPrompt($message, $contextLines);

        $response = Prism::text()
            ->using('ollama', 'gpt-oss:120b')
            ->withPrompt($prompt)
            ->asText();

        return $response->text;
    }

    public function buildContextLines(array $history = []): array
    {
        $lines = [];

        foreach (array_slice($history, -6) as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $role = ($entry['role'] ?? 'user') === 'assistant' ? 'Assistant' : 'User';
            $content = trim((string) ($entry['content'] ?? ''));

            if ($content === '') {
                continue;
            }

            $lines[] = $role . ': ' . $content;
        }

        return $lines;
    }

    public function buildPrompt(string $message, array $contextLines = []): string
    {
        return implode("\n", array_filter([
            // 1 Identity + tone
            'You are the AnimeFeverZone AI assistant.',
            'Be friendly, concise, and helpful.',

            // 2 Public site owner information (RESTRICTED)
            'Site Owner Information (answer ONLY if asked directly):',
            '- Name: Min Khant Naung',
            '- Role: Founder and programmer of AnimeFeverZone',
            '- Company: ConceptX',
            '- Rule: Do NOT mention this information unless the user explicitly asks about the site owner, creator, or developer.',

            // 3 Knowledge boundaries (VERY IMPORTANT)
            'Rules:',
            '- Do NOT invent users, profiles, posts, or site data.',
            '- If you do not know something or lack data, say clearly that you do not know.',
            '- Do not guess or assume.',
            '- You can answer using general anime knowledge and publicly visible site features only.',

            // 4 Formatting rules
            'Formatting Rules:',
            '- You can use basic HTML tags for formatting: <b>, <i>, <ul>, <li>, <p>, <br>',
            '- Do NOT use CSS, scripts, or other tags.',
            '- Keep it concise and readable.',

            // 5 Behavior guidance
            'When a question is unclear, ask a short clarification question.',
            'Use simple formatting and short paragraphs.',

            // 6 Conversation memory (optional but good)
            $contextLines
                ? "Conversation so far:\n" . implode("\n", $contextLines)
                : null,

            // 7 Current user message
            'User: ' . $message,
            'Assistant:',
        ]));
    }
}
