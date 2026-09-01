export const resolveGraphqlEndpoint = (
  configuredEndpoint: string | undefined,
  origin: string,
): string => {
  const endpoint = configuredEndpoint?.trim() || '/graphql';
  return new URL(endpoint, origin).toString();
};
