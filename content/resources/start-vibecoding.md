# Start Vibecoding

This page is intended to help you take your first steps in vibecoding.

We'll be adding more detailed resources and video learning courses over the coming months, but for now, we hope this helps you get started!

## From idea to demo app in three minutes

To give you a sense of just how easy it is to get started, watch this video to see us go from idea to demo app in just three minutes.

<iframe src="https://www.youtube-nocookie.com/embed/wjVqVIz-6FU" title="From idea to demo app in three minutes!" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="aspect-video w-full"></iframe>

## Choosing Your Platform

The vibecoding landscape evolves rapidly. This guide compares the major platforms as of January 2026, focusing on what matters for legal tech builders. These are based on our own experiences as vibecoders and your experiences may differ. Do your own due diligence.

---

## Platform Comparison

### Replit

**What It Is**: A browser-based development environment with AI assistance through Replit Agent, their most capable coding AI yet.

**Best For**: Learning to code while building, educational projects, teams needing collaborative development.

**Strengths**:
- Full development environment in browser - nothing to install
- Can see and edit code directly (builds coding intuition)
- Replit Agent handles complex multi-file projects
- Supports many programming languages
- Built-in hosting and deployment
- Strong collaborative features for teams
- Effort-based pricing means you pay for results, not attempts

**Limitations**:
- Free tier is now primarily for learning (all projects public, limited Agent access)
- Costs can escalate quickly with heavy Agent usage (users report $100-300/month on top of base plans)
- Additional charges for database storage, compute, and data transfer
- Requires Core subscription ($20-25/month) for private projects and full Agent access

**Pricing (January 2026)**:
- **Starter (Free)**: Public projects only, limited Agent trial, 10 dev apps max, basic workspace (1 vCPU, 2 GiB)
- **Core ($20/month annual, $25/month monthly)**: Full Agent 3 access, private repos, $25 monthly usage credits, 4 vCPUs, 8 GiB memory
- **Teams ($35-40/user/month)**: Everything in Core plus 50 viewer seats, centralised billing, role-based access
- **Enterprise**: Custom pricing with SSO, SCIM, advanced compliance

*Note: Heavy Agent usage incurs additional costs. PostgreSQL storage is $1.50/GB/month, app storage $0.03/GB/month.*

**Legal Tech Fit**: Excellent for lawyers who want to understand what's happening technically. The visible code helps build intuition. Agent can handle increasingly complex legal tech projects. Good for learning and prototyping, but watch costs for production use.

**Getting Started**:
1. Sign up at replit.com
2. Create a new Repl (project)
3. Use Replit Agent to describe what you want to build
4. Iterate through conversation
5. Deploy when ready

---

### Lovable

**What It Is**: AI application builder focused on creating production-quality web apps with excellent design. Formerly known as GPT Engineer.

**Best For**: Professional-looking applications with polished UI/UX, client-facing tools.

**Strengths**:
- Exceptional UI/UX design capabilities - creates visually polished applications
- Good component library
- Integrates with Supabase for backend/database
- GitHub integration for version control
- Dev Mode provides full code visibility and editing
- Credits roll over month to month
- Multiplayer editing for collaboration
- Built-in security scans for Supabase apps

**Limitations**:
- Less flexibility for highly unusual requirements
- Backend capabilities less mature than frontend
- Credit-based system means costs vary with usage
- Free tier limited to public projects only

**Pricing (January 2026)**:
- **Free**: 5 credits/day (up to 30/month), public projects only, limited features, up to 5 lovable.app domains
- **Pro ($20-25/month)**: 100 credits/month (rollover), private projects, custom domains, Dev Mode, up to 3 editors, remove Lovable badge
- **Teams ($30/month)**: Pro features plus shared workspace for up to 20 users, centralised billing
- **Business ($42-50/month)**: All Pro features plus SSO, data training opt-out, reusable design templates
- **Enterprise**: Custom pricing with dedicated support, custom API connections, advanced integrations

*Note: Prices shown are starting points - actual cost depends on credit needs. Credit top-ups available on paid plans.*

**Legal Tech Fit**: Best choice when appearance matters - client portals, intake forms, professional dashboards, marketing sites. The design quality out of the box is superior to other platforms. Good for tools you'd show to clients.

**Getting Started**:
1. Visit lovable.dev
2. Describe your app idea in plain language
3. Choose design preferences
4. Iterate on the generated application
5. Connect to Supabase for data persistence
6. Deploy to production

---

### Claude Code (Anthropic)

**What It Is**: Command-line AI coding assistant that works directly in your development environment. Powered by Claude's latest models including Opus 4.5.

**Best For**: Power users who want maximum control, complex multi-file projects, working with existing codebases.

**Strengths**:
- Most sophisticated reasoning of any coding AI (Claude Opus 4.5)
- Works with your existing tools and development setup
- Handles complex, multi-file projects with full context understanding
- Excellent at refactoring, code migration, and working with existing codebases
- No vendor lock-in - your code stays yours
- Can run background tasks (refactoring repos, running test suites)
- Prompt caching reduces costs by up to 90% for repeated context

**Limitations**:
- Requires command-line comfort
- Need your own development environment setup
- Steeper learning curve than browser-based tools
- Must handle your own deployment
- Can get expensive with heavy usage

**Pricing (January 2026)**:
- **Claude Pro ($20/month, $17/month annual)**: Access to Claude Code with standard limits
- **Claude Max Expanded ($100/month)**: 5x usage limits, optimised for Claude Code power users
- **Claude Max Ultimate ($200/month)**: 20x usage limits for heavy development
- **Team ($30/user/month)**: Collaboration features
- **API pricing**: Opus 4.5 at $5/$25 per million tokens (input/output), Sonnet 4.5 at $3/$15, Haiku 4.5 at $1/$5

*Note: Prompt caching can dramatically reduce costs - cache reads cost only 10% of standard input pricing.*

**Legal Tech Fit**: Best choice for complex applications, integrating with existing systems, or when you need sophisticated logic and reasoning. Ideal for lawyers who want to develop genuine technical skills and maintain full control. Opus 4.5 excels at understanding nuanced requirements.

**Getting Started**:
1. Subscribe to Claude Pro or Max at claude.ai
2. Install Claude Code (`npm install -g @anthropic-ai/claude-code`)
3. Navigate to a project directory in your terminal
4. Run `claude` to start
5. Describe what you want to build or change

---

### Google AI Studio

**What It Is**: Google's free interface for building with Gemini AI models, including Gemini 3 Pro and the new agentic coding capabilities.

**Best For**: Integration with Google services, document processing, multimodal applications, budget-conscious experimentation.

**Strengths**:
- AI Studio interface is completely free (no subscription required)
- Access to Gemini 3 Pro with 1-million token context window
- Excellent multimodal capabilities (text, images, code, documents)
- Strong integration with Google Workspace
- Jules asynchronous coding agent for background tasks
- Gemini CLI and Code Assist IDE extensions
- Best-in-class for document understanding and processing

**Limitations**:
- Free tier now primarily a testing ground after December 2025 quota changes
- Less focused on full application building than dedicated platforms
- Production use requires transitioning to paid API
- Rate limits on free tier (5-15 requests/minute depending on model)

**Pricing (January 2026)**:
- **AI Studio**: Free interface, no subscription required
- **Free API Tier**: Access to all models, 5-15 RPM, 250K tokens/minute, 1,000 requests/day - best for testing
- **Paid API Tier**:
  - Gemini 3 Pro: $2-4/million input tokens, $12-18/million output tokens (varies by context length)
  - Gemini 2.5 Pro: $1.25-2.50/million input, $5-10/million output
  - Flash models: $0.075-0.30/million input, $0.30-1.20/million output
- **Google AI Pro ($19.99/month)**: Higher usage limits for Gemini 3 Pro, Deep Search on google.com/ai
- **Google AI Ultra**: Highest access to Gemini 3 Pro, agentic capabilities, advanced features

*Note: December 2025 quota changes significantly reduced free tier viability for production. Plan for paid tier if building anything serious.*

**Legal Tech Fit**: Good for document analysis, processing large volumes of text, and building components that integrate with Google Workspace. The 1-million token context window is particularly valuable for legal documents. Less suited for building complete standalone applications, but powerful for document-centric legal tech.

**Getting Started**:
1. Go to aistudio.google.com
2. Sign in with Google account
3. Experiment with prompts and different models
4. Try the code generation capabilities
5. Use API for integration into applications
6. Consider Gemini CLI for command-line workflows

---

## Comparison Summary

| Platform | Ease of Start | Control | Design Quality | Complex Apps | Free Tier | Best For |
|----------|---------------|---------|----------------|--------------|-----------|----------|
| **Replit** | Medium | High | Medium | Good | Limited | Learning, teams |
| **Lovable** | Easy | Medium | Excellent | Medium | Limited | Client-facing apps |
| **Claude Code** | Hard | Very High | N/A | Excellent | Limited | Complex projects |
| **Google AI Studio** | Easy | High | N/A | Medium | Good (testing) | Document processing |

---

## Which Should You Choose?

### "I've never coded and want the fastest path to a working app"
**Start with: Lovable or Google AI Studio**

### "I want to learn to code while building"
**Start with: Replit**

Seeing the code helps build understanding. Replit's browser-based environment is accessible, and Agent 3 can explain what it's doing. Good investment in your technical skills.

### "I'm comfortable with technical tools and want maximum capability"
**Start with: Claude Code**

The most powerful option for those willing to invest in learning. Claude Opus 4.5's reasoning capabilities are unmatched. Works with any tech stack you prefer.

### "I need to process legal documents or integrate with Google Workspace"
**Start with: Google AI Studio**

The 1-million token context window handles large legal documents easily. Natural fit for Google-centric workflows and document analysis pipelines. Don't upload real documents without considering security, confidentiality and privilege.

### "I'm cost-conscious and want to experiment"
**Start with: Google AI Studio**

The free tier is generous enough for experimentation and learning. Upgrade to paid tier when you're ready to build something real.

---

## General Tips for Getting Started

### 1. Start Small
Your first project shouldn't be a complete practice management system. Build a simple tool:
- A deadline calculator
- A template filler
- A time entry formatter

### 2. Use Plain Language
Don't try to sound technical. Describe what you want like you're explaining to a colleague:

**Good**: "I want a form where I can enter a contract start date and duration in months, and it tells me the end date and any key milestones"

**Less Good**: "Create a React component with date picker inputs and moment.js for date calculations..."

### 3. Iterate, Don't Perfect
Get something working, then improve it. Vibecoding enables rapid iteration. Use it.

### 4. Test with Fake Data
Never use real client data while developing. Create synthetic test cases.

### 5. Document What You Build
Even if it's just notes for yourself. You'll thank yourself later when you need to remember how something works. AI can help with this too.

### 6. Know When to Stop
Some projects will hit walls. It's okay to abandon experiments. That's part of the process.

### 7. Watch Your Costs
Credit-based and usage-based pricing can add up quickly. Set budgets and monitor usage, especially when experimenting heavily.
