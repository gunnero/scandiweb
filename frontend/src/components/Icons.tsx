import type { SVGProps } from 'react';

type IconProps = SVGProps<SVGSVGElement>;

const sharedProps = {
  fill: 'none',
  stroke: 'currentColor',
  strokeLinecap: 'round' as const,
  strokeLinejoin: 'round' as const,
  viewBox: '0 0 24 24',
};

export function ShoppingCartIcon(props: IconProps) {
  return (
    <svg width={24} height={24} strokeWidth={2} {...sharedProps} {...props}>
      <path d="M3 4h2l2.4 10.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6" />
      <circle cx="10" cy="20" r="1" />
      <circle cx="18" cy="20" r="1" />
    </svg>
  );
}

export function ChevronLeftIcon(props: IconProps) {
  return (
    <svg width={24} height={24} strokeWidth={2} {...sharedProps} {...props}>
      <path d="m15 18-6-6 6-6" />
    </svg>
  );
}

export function ChevronRightIcon(props: IconProps) {
  return (
    <svg width={24} height={24} strokeWidth={2} {...sharedProps} {...props}>
      <path d="m9 18 6-6-6-6" />
    </svg>
  );
}
