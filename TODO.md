# TODO - Assert Library Improvements

Based on analysis of the webmozart/assert fork, here are improvements we should implement in our fork:

## High Priority

### 1. Add Static Analysis Support
- [ ] Add Psalm assertion syntax annotations to all methods
- [ ] Create or integrate PHPStan plugin for proper type inference
- [ ] Ensure all assertions have proper return type annotations
- [ ] Add @psalm-assert and @psalm-assert-if-true annotations where applicable

### 2. Improve Extensibility
- [ ] Add protected method `valueToString($value)` for custom value representation
- [ ] Add protected method `typeToString($value)` for custom type representation
- [ ] Add protected method `strlen($value)` for custom string length calculation
- [ ] Add protected method `reportInvalidArgument($message)` for custom error handling
- [ ] Document how to properly extend the Assert class

## Low Priority

### 3. Simplify Architecture
- [ ] Consider consolidating multiple files into fewer, more focused files
- [ ] Evaluate if we need separate Assert and Assertion classes
- [ ] Review if LazyAssertion could be simplified

### 4. Add Convenience Features
- [ ] Consider adding a debug mode with more verbose error information

### 5. Documentation Improvements
- [ ] Add examples for each assertion method
- [ ] Create a migration guide from beberlei/assert
- [ ] Document best practices for using assertions
- [ ] Add performance considerations documentation

## Breaking Changes to Consider (Major Version)
- [ ] Remove deprecated `isTraversable()` in favor of `isIterable()`
- [ ] Standardize method naming conventions (e.g., `lengthBetween` vs `betweenLength`)
- [ ] Consider removing redundant methods or consolidating similar ones

## Testing & Quality
- [ ] Ensure 100% test coverage for all new assertions
- [ ] Add performance benchmarks comparing to beberlei and webmozart
- [ ] Create integration tests with popular frameworks
- [ ] Set up mutation testing to ensure test quality

## Notes
- Maintain backwards compatibility where possible
- Consider creating a compatibility layer for easy migration
- Prioritize developer experience and clear error messages
- Keep performance in mind - assertions should be fast
