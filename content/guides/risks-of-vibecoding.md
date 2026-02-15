# Risks of Vibecoding

## Why This Section Matters

Vibecoding democratises software development, but it also introduces risks that traditional developers have spent decades learning to mitigate. As a legal professional, you already understand risk management. Apply that same rigour here.

This isn't meant to discourage you. It's meant to make you a responsible vibecoder.

---

## Technical Risks

### 1. Code You Don't Understand

**The Risk**: AI generates code that works but you can't explain why. When it breaks, you can't fix it.

**Why It Matters for Legal Tech**: A document generator that produces incorrect output could result in professional liability. If you can't diagnose why it happened, you can't prevent it recurring.

**Mitigation**:
- Ask the AI to explain what the code does in plain language
- Request comments in the code
- Test extensively before any real-world use
- Have a developer review critical applications

### 2. AI Hallucinations

**The Risk**: Large language models can generate plausible-looking code that is fundamentally wrong. The AI may:
- Invent function names that don't exist
- Use outdated syntax or deprecated methods
- Create logic that appears correct but produces wrong results
- Reference libraries or APIs incorrectly

**Why It Matters for Legal Tech**: A statute citation tool that confidently generates fake case references is worse than no tool at all.

**Mitigation**:
- Verify outputs against known correct results
- Test with edge cases
- Never assume generated code is correct
- Use AI models with larger context windows and more recent training data

### 3. Security Vulnerabilities

**The Risk**: AI-generated code may contain security flaws including:
- SQL injection vulnerabilities
- Cross-site scripting (XSS)
- Authentication bypasses
- Data exposure
- Insecure data storage
- Hard-coded credentials

**Why It Matters for Legal Tech**: Legal data is among the most sensitive. A security breach could expose privileged communications, violate data protection laws, and destroy client trust.

**Mitigation**:
- Never deploy vibecoded applications to production without security review
- Use established authentication providers rather than building your own
- Never store sensitive data in vibecoded applications
- Keep applications internal/sandboxed where possible
- Assume every input could be malicious

### 4. Dependency and Supply Chain Risks

**The Risk**: AI may incorporate third-party libraries or packages that:
- Contain their own vulnerabilities
- Are no longer maintained
- Have licensing restrictions
- Could be compromised in future updates

**Why It Matters for Legal Tech**: Your application's security is only as strong as its weakest dependency.

**Mitigation**:
- Understand what packages are being used
- Prefer well-maintained, widely-used libraries
- Lock dependency versions
- Audit dependencies for known vulnerabilities

### 5. Scalability and Performance

**The Risk**: Code that works for 10 users may fail catastrophically at 1,000. AI often generates "happy path" code that doesn't handle:
- High concurrent usage
- Large data volumes
- Network failures
- Database timeouts

**Why It Matters for Legal Tech**: An e-discovery tool that crashes mid-review could lose critical work or corrupt data.

**Mitigation**:
- Load test before any significant deployment
- Implement proper error handling
- Use managed services that handle scaling
- Design for failure (save frequently, enable recovery)

---

## Professional and Ethical Risks

### 6. Confidentiality and Privilege

**The Risk**: Using vibecoding tools with client data may compromise:
- **Attorney-client privilege**: Information shared with AI providers may waive privilege
- **Confidentiality obligations**: AI training data practices vary by provider
- **Work product protection**: Generated outputs may not be protected

**Why It Matters for Legal Tech**: This is an existential risk.

**Mitigation**:
- **NEVER input confidential client data into vibecoding tools**
- Use synthetic or anonymised data for development
- Review AI provider terms regarding data retention and training
- Consider enterprise AI solutions with enhanced privacy commitments
- Consult your bar's ethics guidance on AI use

### 7. Competence and Supervision

**The Risk**: Bar rules require lawyers to provide competent representation. Using technology you don't understand may breach this duty.

Lawyers must:
- Understand AI capabilities and limitations
- Maintain competence in technology affecting their practice
- Supervise AI use as they would any other tool

**Why It Matters for Legal Tech**: "The AI made an error" is not a defence to malpractice.

**Mitigation**:
- Understand how your tools work at a conceptual level
- Verify all outputs before relying on them
- Maintain human oversight of AI-assisted work
- Document your review process

### 8. Unauthorised Practice of Law

**The Risk**: Legal tech tools that provide legal advice or draft legal documents could:
- Constitute unauthorised practice if used without lawyer supervision
- Expose users to reliance on incorrect information
- Create liability for the tool creator

**Why It Matters for Legal Tech**: Even well-intentioned tools can cross ethical lines.

**Mitigation**:
- Include clear disclaimers that tools don't provide legal advice
- Design tools to assist lawyers, not replace them
- Require lawyer review of outputs
- Consider jurisdiction-specific UPL rules

### 9. Bias and Fairness

**The Risk**: AI models reflect biases in their training data. Legal tech tools may:
- Produce biased risk assessments
- Favour certain outcomes based on demographic factors
- Perpetuate historical inequities in the legal system

**Why It Matters for Legal Tech**: Biased tools in criminal justice, lending, employment, or other areas can cause real harm to real people.

**Mitigation**:
- Test tools across diverse scenarios
- Be sceptical of AI risk scoring
- Maintain human decision-making for consequential matters
- Monitor outcomes for disparate impact

---

## Business and Operational Risks

### 10. Vendor Lock-In and Dependency

**The Risk**: Your application may depend on:
- Specific AI providers whose terms or pricing may change
- Platforms that could shut down
- APIs that may be deprecated

**Why It Matters for Legal Tech**: Client systems need long-term stability.

**Mitigation**:
- Understand your dependencies
- Have contingency plans
- Export and backup data regularly
- Avoid single points of failure

### 11. Intellectual Property Uncertainty

**The Risk**: Questions remain about:
- Who owns AI-generated code
- Whether AI output can be copyrighted
- Liability for AI that reproduces copyrighted material

**Why It Matters for Legal Tech**: Commercialising AI-generated tools has unresolved IP implications.

**Mitigation**:
- Review AI provider terms regarding output ownership
- Document your creative contributions
- Consider IP insurance for commercial products
- Stay informed as law evolves

### 12. Maintenance Burden

**The Risk**: Software requires ongoing maintenance:
- Security patches
- Dependency updates
- Bug fixes
- Feature additions

If you built it quickly without understanding, maintaining it will be difficult.

**Why It Matters for Legal Tech**: Abandoned tools become security liabilities.

**Mitigation**:
- Document what you build
- Plan for ongoing maintenance
- Consider whether you'll maintain or sunset each tool
- Don't create tools you can't support

---

## Risk Assessment Framework

Before building or deploying any vibecoded legal tech, ask:

### Impact Assessment
1. What's the worst case if this tool fails?
2. Who could be harmed and how?
3. Is this tool handling sensitive data?
4. What are the professional responsibility implications?

### Technical Assessment
5. Have I tested this thoroughly?
6. Has anyone with technical expertise reviewed it?
7. What dependencies does it have?
8. How will it be maintained?

### Deployment Decision
9. Is this for internal use only or client-facing?
10. Am I treating this as experimental or production?
11. What disclaimers and limitations are appropriate?
12. Who approved this use?

---

## The Bottom Line

Vibecoding is transformative, but it's not a shortcut to production-ready software. Every tool you build should be treated as a prototype until proven otherwise through:

- Rigorous testing
- Security review
- Human oversight
- Appropriate use limitations

The lawyers who will thrive in the vibecoding era are those who combine its creative power with professional responsibility and healthy scepticism.
