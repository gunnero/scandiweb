interface AsyncStateProps {
  title: string;
  message?: string;
  actionLabel?: string;
  onAction?: () => void;
  isLoading?: boolean;
}

export function AsyncState({
  title,
  message,
  actionLabel,
  onAction,
  isLoading = false,
}: AsyncStateProps) {
  return (
    <section className="async-state" aria-live="polite" aria-busy={isLoading}>
      {isLoading && <span className="loading-spinner" aria-hidden="true" />}
      <h1>{title}</h1>
      {message && <p>{message}</p>}
      {actionLabel && onAction && (
        <button className="secondary-button" type="button" onClick={onAction}>
          {actionLabel}
        </button>
      )}
    </section>
  );
}
