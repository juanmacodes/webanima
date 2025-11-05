export type GraphQLRequest<TVariables = Record<string, unknown>> = {
  query: string;
  variables?: TVariables;
  revalidate?: number;
  tags?: string[];
};

export async function gqlFetch<TData, TVariables = Record<string, unknown>>({
  query,
  variables,
  revalidate = 60,
  tags
}: GraphQLRequest<TVariables>): Promise<TData> {
  const endpoint = process.env.NEXT_PUBLIC_WP ? `${process.env.NEXT_PUBLIC_WP}/graphql` : undefined;
  if (!endpoint) {
    throw new Error('NEXT_PUBLIC_WP env variable must be set to use gqlFetch');
  }

  const response = await fetch(endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ query, variables }),
    next: { revalidate, tags }
  });

  if (!response.ok) {
    const message = await response.text();
    throw new Error(`GraphQL request failed: ${response.status} ${response.statusText} - ${message}`);
  }

  const json = await response.json();
  if (json.errors) {
    throw new Error(json.errors.map((error: { message: string }) => error.message).join('\n'));
  }

  return json.data as TData;
}
