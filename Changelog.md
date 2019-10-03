# Changelog
All notable changes to this project will be documented in this file.

## [Unreleased]
## Fixed
- Revert back to `method_exists()` instead of `is_callable()` to prevent calling non-existant methods.

## [1.0.1] - 2019-10-03
### Changed
- Remove dead code in `SmartyRenderer::setHelperPluginManager()`
- Use `is_callable()` before using `call_user_func_array`


## [1.0.0] - 2019-08-14
### Added
- Initial version.

[Unreleased]: https://github.com/gkralik/zf3-smarty-module/compare/1.0.1...HEAD
[1.0.1]: https://github.com/gkralik/zf3-smarty-module/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/gkralik/zf3-smarty-module/releases/tag/1.0.0
