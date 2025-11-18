import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";
import {my_company_name} from "@/composables/GlobalComposables";
import {proposal_view_url} from "@/composables/ProposalComposable";
import {OfferedMonthlyCost} from "@/composables/ProductComposable";
import {GetProductsAsString} from "@/composables/ProductComposable";

const store = GlobalStore();
const {SetState,ShowError} = store;
const {proposal,profile} = storeToRefs(store);

// The internal prompter
export const Prompt = async(token:string, system_prompt:string, user_prompt = "") => {
  SetState({llm_fetching: true});
  const form = new FormData;
  const messages = [{role:"system", content: system_prompt}];
  if (user_prompt.length) {
    messages.push({
      role: 'user', content: user_prompt,
    });
  }
  form.append('history', JSON.stringify(messages));

  return ProposalServer(token).post('/prompt/generate',form).then(res => {
    return res.data;
  }).finally(() => {
    SetState({ llm_fetching: false });
  });
}

// The external functions
export async function GenerateCoverLetter(token:string) {
  if (!GetProductsAsString().length) {
    return ShowError("Please select at least 1 product");
  }
  const system_prompt = (`
    You are a helpful assistant that writes warm, professional, and persuasive cover letters to a ccompany lease proposals for business equipment or IT Services.
    Your task is to draft a personalized cover letter to be included in a formal proposal sent to a prospective client.
    The tone should be professional and courteous, yet friendly and approachable—making the recipient feel confident in doing business.

    Proposal Details:
    - Product(s): ${GetProductsAsString()}
    - First name: ${proposal.value.first_name}
    - Last name: ${proposal.value.last_name}
    - Company name: ${proposal.value.company_name}
    - Proposal Author: ${profile?.value?.name}
    - Proposal Author Company: ${my_company_name.value}
    - Monthly Payment Offer: ${OfferedMonthlyCost(proposal.value)} per month
    - Lease Term Offer: ${proposal.value.lease_term_offered} months

    Context and Guidelines:
    - Personalize the letter using the client's first name and company name naturally in the body.
    - Mention the proposed product(s) in a professional, benefit-driven way.
    - Your letter should briefly introduce the leasing offer, express appreciation for the opportunity, and highlight the value and reliability of the equipment being proposed.
    - Do not go into technical specifications, but feel free to mention benefits like reliability, suitability for business environments, and long-term support.
    - Try to mention Monthly Payment Offer and Lease Term Offer in a way that they sound appealing and beneficial to the client.
    - Invite the client to reach out with any questions or clarifications.
    - Always end on a positive and professional note.
    - Keep the letter concise: around 150–250 words.

    Output Instructions:
    - Return the entire letter formatted as valid HTML, ready to be pasted into a WYSIWYG editor.
    - Create new lines using <br> tags for each distinct thought or section (e.g., greeting, intro, body, sign-off) for proper spacing between paragraphs.
    - Every new though MUST be separated with two new <br> lines.
    - Use <br> tags to force spacing between paragraphs especially after "Hello Jesse," and before "best regards" should have 2 blank new lines
    - Use <strong> or <em> where emphasis is needed, if relevant.
    - Begin directly with the greeting (e.g., <p>Hello Jesse,</p>) and end with a proper sign-off (e.g., <p>Best regards,<br>[Author Name]<br>[Author Company]</p>).
    - Highlight the product names in <strong>
    - Do not nest <p> tags, and avoid putting multiple paragraphs inside one <p>.
    - Do not include any meta-comments, markdown, explanations, or placeholder tags.
    - Do not wrap the entire letter in <html>, <body>, or <div> unless specifically part of the letter’s style.
  `);
  return await Prompt(token, system_prompt);
}
export async function GenerateTitle(token:string) {
  const system_prompt = (`
    You are an assistant that generates concise and professional proposal titles based on minimal input.
    Your task is to generate a clear, business-friendly title for a lease proposal document. The title should reflect the product(s) being offered and the client company it is prepared for.

    Proposal Details:
    - Product(s): ${GetProductsAsString()}
    - Lead Company name: ${proposal.value.company_name}

    Guidelines:
    - The title must include the the Lead Company Name.
    - Use a professional and formal tone. Avoid marketing fluff.
    - Keep the title under 10 words.
    - If Product(s) are provided, ONLY include the Product's BRAND
    - Do not include placeholder tags or brackets.
    - Do not explain your answer—return only the plain title.
    - Do not assume or provide an answer outside of given data.
    - Do not say it is an agreement, it's just a Proposal.
    - If NO PRODUCT was given, suggest a title e.g. "Proposal for [lead company name]"
    - The very ideal sounding title would be "[product brand] Proposal for [lead company name]"
  `);
  return await Prompt(token, system_prompt);
}
export async function GenerateEmailBody(token:string) {
  const system_prompt = (`
    You are a helpful assistant that writes warm, professional, and persuasive email messages to accompany lease proposals for business equipment, usually multifunction printers or copiers.
    Your task is to draft a personalized email body to be included in a formal message sent to a prospective client. The tone should be professional and courteous, yet friendly and approachable—making the recipient feel confident in doing business.

    Proposal Details:
    - Product(s): ${GetProductsAsString()}
    - Contact First name: ${proposal.value.first_name}
    - Contact Company name: ${proposal.value.company_name}
    - Proposal Author: ${profile?.value?.name}
    - Proposal Author Company: ${my_company_name.value}
    - Proposal Link: <br><a href="${proposal_view_url.value}">View Proposal</a><br>

    Context and Guidelines:
    - Personalize the email using the client's first name and company name naturally in the body.
    - Mention the proposed product(s) in a professional, benefit-driven way.
    - Invite the client to reach out with any questions or clarifications.
    - Always end on a positive and professional note.
    - Keep the message concise: around 50 words.
    - Include the Proposal Link in the email body, and mention it in the email content.
    - Assume this content will be pasted as the email body, not as an attachment or separate letter.

    Output Instructions:
    - Return the entire email body formatted as valid HTML, ready to be pasted directly into an email or WYSIWYG editor.
    - Create new lines using <br> tags for each distinct thought or section (e.g., greeting, intro, body, sign-off) for proper spacing between paragraphs.
    - Every new thought MUST be separated with two new <br> tags.
    - Use <strong> or <em> where emphasis is needed, if relevant.
    - Begin directly with the greeting (e.g., <p>Hello Jesse,</p>) and end with a proper sign-off (e.g., <p>Best regards,<br>[Author Name]<br>[Author Company]</p>).
    - Highlight the product names in <strong>.
    - Do not nest <p> tags, and avoid putting multiple paragraphs inside one <p>.
    - Do not include any meta-comments, markdown, explanations, or placeholder tags.
    - Do not wrap the entire message in <html>, <body>, or <div> unless specifically part of the message’s style.
  `);
  return await Prompt(token, system_prompt);
}
