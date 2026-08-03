export function removeTrailingCount(text) {
  if (!text) return text;

  return text.replace(/\s*\(\d+\)\s*$/, '');
}
