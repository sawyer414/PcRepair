import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";

const SUPABASE_URL = "https://estqnicukbhhlvkduuca.supabase.co";
// Publishable (anon) key — safe to include in client-side code.
// Row-Level Security in the Supabase dashboard controls data access.
const SUPABASE_ANON_KEY =
  "sb_publishable_55mzdM2170-oWYMV5wFqYA_Rpe3Ws63";

export const supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

export const CONTACT_TABLE = "ticket";
