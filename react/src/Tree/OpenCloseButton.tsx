type Props = {
  onClick: () => void;
  open: boolean;
  hasChildren: boolean;
};

const OpenCloseButton = ({ onClick, open, hasChildren }: Props) => {
  if (!hasChildren) {
    return <span className="no-open-close-button" />;
  }

  return (
    <button className="open-close-button" onClick={onClick}>
      {open ? (
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
          className="open-close-button-icon"
        >
          <rect width="18" height="18" x="3" y="3" rx="2" />
          <path d="M8 12h8" />
        </svg>
      ) : (
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
          className="open-close-button-icon"
        >
          <rect width="18" height="18" x="3" y="3" rx="2" />
          <path d="M8 12h8" />
          <path d="M12 8v8" />
        </svg>
      )}
    </button>
  );
};

export default OpenCloseButton;
