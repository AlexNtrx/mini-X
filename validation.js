const validation = new JustValidate('#form-signup');

validation
  .addField('#username-signup', [
    {
      rule: 'required',
      errorMessage: 'Username is required',
    },
  ])
  .addField('#password-signup', [
    {
      rule: 'required',
      errorMessage: 'Password is required',
    },
  ])
  .addField('#email-signup', [
    {
      rule: 'required',
      errorMessage: 'Email is required',
    },
    {
      rule: 'email',
      errorMessage: 'Email is not valid',
    },
  ])
  .onSuccess((event) => {
    event.target.submit();
  });