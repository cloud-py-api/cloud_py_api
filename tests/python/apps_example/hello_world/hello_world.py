def function_call_example(arg1, srg2):
    print(f'function_call_example:<{arg1}> and <{srg2}>')


print('This is string will be executed during import!')


def function_hello_world():
    print(f'function_hello_world!')
    return 'WOW!'


if __name__ == '__main__':
    print('This will not be executed at all')
